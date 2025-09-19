<?php

namespace App\Livewire\Products;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Franchise;
use Illuminate\Support\Facades\DB;

class CrossFranchiseSearch extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedCategory = '';
    public $selectedFranchise = '';
    public $sortBy = 'name';
    public $sortDirection = 'asc';
    public $showOnlyAvailable = false;
    public $requestedProducts = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedCategory' => ['except' => ''],
        'selectedFranchise' => ['except' => ''],
        'sortBy' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
    ];

    public function mount()
    {
        // Inicializar lista de produtos solicitados da sessão
        $this->requestedProducts = session('requested_products', []);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedCategory()
    {
        $this->resetPage();
    }

    public function updatedSelectedFranchise()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function requestProduct($productId, $franchiseId, $requestedQuantity = 1)
    {
        $product = Product::find($productId);
        $franchise = Franchise::find($franchiseId);

        if (!$product || !$franchise) {
            session()->flash('error', 'Produto ou franquia não encontrada.');
            return;
        }

        // Verificar se já foi solicitado
        $key = $productId . '_' . $franchiseId;
        if (isset($this->requestedProducts[$key])) {
            session()->flash('error', 'Produto já solicitado desta franquia.');
            return;
        }

        // Adicionar à lista de solicitações
        $this->requestedProducts[$key] = [
            'product_id' => $productId,
            'product_name' => $product->name,
            'franchise_id' => $franchiseId,
            'franchise_name' => $franchise->name,
            'requested_quantity' => $requestedQuantity,
            'requested_at' => now()->toDateTimeString(),
        ];

        // Salvar na sessão
        session(['requested_products' => $this->requestedProducts]);

        // Aqui você pode implementar notificação para a franquia de origem
        $this->sendTransferRequest($productId, $franchiseId, $requestedQuantity);

        session()->flash('success', "Solicitação enviada para {$franchise->name}!");
    }

    public function removeRequest($key)
    {
        unset($this->requestedProducts[$key]);
        session(['requested_products' => $this->requestedProducts]);
        session()->flash('success', 'Solicitação removida.');
    }

    private function sendTransferRequest($productId, $sourceFranchiseId, $quantity)
    {
        // Criar registro de solicitação de transferência
        DB::table('stock_transfer_requests')->insert([
            'product_id' => $productId,
            'from_franchise_id' => $sourceFranchiseId,
            'to_franchise_id' => auth()->user()->franchise_id,
            'requested_quantity' => $quantity,
            'status' => 'pending',
            'requested_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Aqui você pode implementar notificação em tempo real
        // event(new StockTransferRequested($productId, $sourceFranchiseId, auth()->user()->franchise_id));
    }

    public function render()
    {
        $currentFranchiseId = auth()->user()->franchise_id;

        // Query principal
        $query = Product::query()
            ->select([
                'products.*',
                'inventories.quantity',
                'inventories.location',
                'inventories.franchise_id',
                'franchises.name as franchise_name',
                'franchises.phone as franchise_phone'
            ])
            ->join('inventories', 'products.id', '=', 'inventories.product_id')
            ->join('franchises', 'inventories.franchise_id', '=', 'franchises.id')
            ->where('inventories.franchise_id', '!=', $currentFranchiseId); // Excluir produtos da própria franquia

        // Filtros
        if ($this->search) {
            $query->where(function($q) {
                $q->where('products.name', 'like', "%{$this->search}%")
                    ->orWhere('products.sku', 'like', "%{$this->search}%")
                    ->orWhere('franchises.name', 'like', "%{$this->search}%");
            });
        }

        if ($this->selectedCategory) {
            $query->where('products.category', $this->selectedCategory);
        }

        if ($this->selectedFranchise) {
            $query->where('inventories.franchise_id', $this->selectedFranchise);
        }

        if ($this->showOnlyAvailable) {
            $query->where('inventories.quantity', '>', 0);
        }

        // Ordenação
        $query->orderBy($this->sortBy === 'franchise_name' ? 'franchises.name' : 'products.' . $this->sortBy, $this->sortDirection);

        $products = $query->paginate(12);

        // Dados para filtros
        $categories = Product::distinct()->pluck('category')->filter();
        $franchises = Franchise::where('id', '!=', $currentFranchiseId)->orderBy('name')->get();

        return view('livewire.products.cross-franchise-search', [
            'products' => $products,
            'categories' => $categories,
            'franchises' => $franchises,
        ]);
    }
}

// Migration para a tabela de solicitações de transferência
/*
Schema::create('stock_transfer_requests', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->foreignId('from_franchise_id')->constrained('franchises')->cascadeOnDelete();
    $table->foreignId('to_franchise_id')->constrained('franchises')->cascadeOnDelete();
    $table->integer('requested_quantity');
    $table->integer('approved_quantity')->nullable();
    $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
    $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
    $table->foreignId('approved_by')->nullable()->constrained('users')->cascadeOnDelete();
    $table->text('notes')->nullable();
    $table->timestamp('approved_at')->nullable();
    $table->timestamps();

    $table->index(['from_franchise_id', 'status']);
    $table->index(['to_franchise_id', 'status']);
});
*/
