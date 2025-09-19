<?php

namespace App\Livewire\Inventory;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Franchise;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class InventoryManager extends Component
{
    use WithPagination, AuthorizesRequests;

    public $search = '';
    public $selectedCategory = '';
    public $stockFilter = 'all'; // all, low, out, critical
    public $showMovementModal = false;
    public $showAdjustmentModal = false;
    public $showTransferModal = false;

    // Modal data
    public $selectedProduct = null;
    public $movementType = 'in'; // in, out, adjustment, transfer
    public $quantity = 0;
    public $reason = '';
    public $notes = '';
    public $targetFranchise = '';
    public $newMinStock = 0;
    public $newLocation = '';

    protected $listeners = [
        'inventoryUpdated' => '$refresh',
        'showAdjustment' => 'openAdjustmentModal',
        'showTransfer' => 'openTransferModal',
    ];

    public function mount()
    {
        $this->authorize('viewAny', Inventory::class);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedCategory()
    {
        $this->resetPage();
    }

    public function updatedStockFilter()
    {
        $this->resetPage();
    }

    // Movimentação de estoque
    public function openMovementModal($productId, $type = 'in')
    {
        $this->selectedProduct = Product::find($productId);
        $this->movementType = $type;
        $this->quantity = 0;
        $this->reason = '';
        $this->notes = '';
        $this->showMovementModal = true;
    }

    public function processMovement()
    {
        $this->validate([
            'quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        $inventory = Inventory::firstOrCreate([
            'product_id' => $this->selectedProduct->id,
            'franchise_id' => $user->franchise_id,
        ], [
            'quantity' => 0,
            'min_stock' => 10,
            'location' => 'Estoque Principal'
        ]);

        DB::beginTransaction();
        try {
            // Atualizar estoque
            if ($this->movementType === 'in') {
                $inventory->increment('quantity', $this->quantity);
                $movementType = 'entrada';
            } else {
                if ($inventory->quantity < $this->quantity) {
                    throw new \Exception('Quantidade insuficiente em estoque');
                }
                $inventory->decrement('quantity', $this->quantity);
                $movementType = 'saida';
            }

            // Registrar movimento
            StockMovement::create([
                'product_id' => $this->selectedProduct->id,
                'franchise_id' => $user->franchise_id,
                'type' => $movementType,
                'quantity' => $this->quantity,
                'previous_quantity' => $inventory->quantity + ($this->movementType === 'in' ? -$this->quantity : $this->quantity),
                'new_quantity' => $inventory->quantity,
                'reason' => $this->reason,
                'notes' => $this->notes,
                'user_id' => $user->id,
            ]);

            DB::commit();
            $this->showMovementModal = false;
            $this->dispatch('inventoryUpdated');
            session()->flash('success', 'Movimento de estoque registrado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erro: ' . $e->getMessage());
        }
    }

    // Ajuste de estoque
    public function openAdjustmentModal($productId)
    {
        $this->selectedProduct = Product::find($productId);
        $inventory = Inventory::where('product_id', $productId)
            ->where('franchise_id', auth()->user()->franchise_id)
            ->first();

        $this->quantity = $inventory ? $inventory->quantity : 0;
        $this->newMinStock = $inventory ? $inventory->min_stock : 10;
        $this->newLocation = $inventory ? $inventory->location : 'Estoque Principal';
        $this->reason = '';
        $this->notes = '';
        $this->showAdjustmentModal = true;
    }

    public function processAdjustment()
    {
        $this->validate([
            'quantity' => 'required|numeric|min:0',
            'newMinStock' => 'required|numeric|min:0',
            'newLocation' => 'required|string|max:100',
            'reason' => 'required|string|max:255',
        ]);

        $user = auth()->user();
        $inventory = Inventory::firstOrCreate([
            'product_id' => $this->selectedProduct->id,
            'franchise_id' => $user->franchise_id,
        ], ['quantity' => 0]);

        $previousQuantity = $inventory->quantity;

        DB::beginTransaction();
        try {
            // Atualizar inventário
            $inventory->update([
                'quantity' => $this->quantity,
                'min_stock' => $this->newMinStock,
                'location' => $this->newLocation,
            ]);

            // Registrar movimento
            StockMovement::create([
                'product_id' => $this->selectedProduct->id,
                'franchise_id' => $user->franchise_id,
                'type' => 'ajuste',
                'quantity' => abs($this->quantity - $previousQuantity),
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $this->quantity,
                'reason' => $this->reason,
                'notes' => $this->notes,
                'user_id' => $user->id,
            ]);

            DB::commit();
            $this->showAdjustmentModal = false;
            $this->dispatch('inventoryUpdated');
            session()->flash('success', 'Ajuste realizado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erro: ' . $e->getMessage());
        }
    }

    // Transferência entre franquias
    public function openTransferModal($productId)
    {
        $this->selectedProduct = Product::find($productId);
        $this->quantity = 0;
        $this->targetFranchise = '';
        $this->reason = '';
        $this->notes = '';
        $this->showTransferModal = true;
    }

    public function processTransfer()
    {
        $this->validate([
            'quantity' => 'required|numeric|min:0.01',
            'targetFranchise' => 'required|exists:franchises,id',
            'reason' => 'required|string|max:255',
        ]);

        $user = auth()->user();
        $fromInventory = Inventory::where('product_id', $this->selectedProduct->id)
            ->where('franchise_id', $user->franchise_id)
            ->first();

        if (!$fromInventory || $fromInventory->quantity < $this->quantity) {
            session()->flash('error', 'Quantidade insuficiente em estoque');
            return;
        }

        DB::beginTransaction();
        try {
            // Remover do estoque origem
            $fromInventory->decrement('quantity', $this->quantity);

            // Adicionar ao estoque destino
            $toInventory = Inventory::firstOrCreate([
                'product_id' => $this->selectedProduct->id,
                'franchise_id' => $this->targetFranchise,
            ], ['quantity' => 0, 'min_stock' => 10]);

            $toInventory->increment('quantity', $this->quantity);

            // Registrar movimentos
            StockMovement::create([
                'product_id' => $this->selectedProduct->id,
                'franchise_id' => $user->franchise_id,
                'type' => 'transferencia_saida',
                'quantity' => $this->quantity,
                'previous_quantity' => $fromInventory->quantity + $this->quantity,
                'new_quantity' => $fromInventory->quantity,
                'reason' => $this->reason,
                'notes' => $this->notes,
                'user_id' => $user->id,
                'reference_franchise_id' => $this->targetFranchise,
            ]);

            StockMovement::create([
                'product_id' => $this->selectedProduct->id,
                'franchise_id' => $this->targetFranchise,
                'type' => 'transferencia_entrada',
                'quantity' => $this->quantity,
                'previous_quantity' => $toInventory->quantity - $this->quantity,
                'new_quantity' => $toInventory->quantity,
                'reason' => $this->reason,
                'notes' => $this->notes,
                'user_id' => $user->id,
                'reference_franchise_id' => $user->franchise_id,
            ]);

            DB::commit();
            $this->showTransferModal = false;
            $this->dispatch('inventoryUpdated');
            session()->flash('success', 'Transferência realizada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erro: ' . $e->getMessage());
        }
    }

    public function generateStockReport()
    {
        $user = auth()->user();

        $data = Inventory::with(['product', 'franchise'])
            ->when($user->role !== 'super_admin', function($q) use ($user) {
                $q->where('franchise_id', $user->franchise_id);
            })
            ->get()
            ->map(function($item) {
                return [
                    'franchise' => $item->franchise->name,
                    'product' => $item->product->name,
                    'sku' => $item->product->sku,
                    'category' => $item->product->category,
                    'quantity' => $item->quantity,
                    'min_stock' => $item->min_stock,
                    'location' => $item->location,
                    'status' => $item->quantity <= $item->min_stock ? 'BAIXO' : 'OK',
                ];
            });

        // Aqui você pode implementar exportação para Excel/PDF
        return response()->streamDownload(function() use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Franquia', 'Produto', 'SKU', 'Categoria', 'Quantidade', 'Estoque Mín.', 'Localização', 'Status']);

            foreach ($data as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 'relatorio-estoque-' . now()->format('Y-m-d') . '.csv');
    }

    public function render()
    {
        $user = auth()->user();

        $query = Inventory::with(['product', 'franchise'])
            ->when($user->role !== 'super_admin', function($q) use ($user) {
                $q->where('franchise_id', $user->franchise_id);
            });

        // Filtros
        if ($this->search) {
            $query->whereHas('product', function($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('sku', 'like', "%{$this->search}%");
            });
        }

        if ($this->selectedCategory) {
            $query->whereHas('product', function($q) {
                $q->where('category', $this->selectedCategory);
            });
        }

        switch ($this->stockFilter) {
            case 'low':
                $query->whereRaw('quantity <= min_stock');
                break;
            case 'out':
                $query->where('quantity', '<=', 0);
                break;
            case 'critical':
                $query->whereRaw('quantity <= (min_stock * 0.5)');
                break;
        }

        $inventories = $query->orderBy('quantity', 'asc')->paginate(20);

        // Estatísticas
        $stats = [
            'total_products' => Inventory::when($user->role !== 'super_admin', fn($q) => $q->where('franchise_id', $user->franchise_id))->count(),
            'low_stock' => Inventory::when($user->role !== 'super_admin', fn($q) => $q->where('franchise_id', $user->franchise_id))->whereRaw('quantity <= min_stock')->count(),
            'out_of_stock' => Inventory::when($user->role !== 'super_admin', fn($q) => $q->where('franchise_id', $user->franchise_id))->where('quantity', '<=', 0)->count(),
            'total_value' => Inventory::with('product')->when($user->role !== 'super_admin', fn($q) => $q->where('franchise_id', $user->franchise_id))->get()->sum(fn($item) => $item->quantity * $item->product->price),
        ];

        $categories = Product::distinct()->pluck('category')->filter();
        $franchises = Franchise::when($user->role !== 'super_admin', fn($q) => $q->where('id', $user->franchise_id))->get();

        return view('livewire.inventory.manager', [
            'inventories' => $inventories,
            'stats' => $stats,
            'categories' => $categories,
            'franchises' => $franchises,
        ]);
    }
}




