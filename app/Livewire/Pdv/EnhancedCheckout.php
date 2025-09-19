<?php

namespace App\Livewire\Pdv;

use Livewire\Component;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Inventory;
use App\Models\Client;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EnhancedCheckout extends Component
{
    use AuthorizesRequests;

    // Busca e seleção
    public $search = '';
    public $selectedCategory = 'all';
    public $selectedClient = null;
    public $clientSearch = '';

    // Carrinho
    public $cart = [];
    public $services = []; // Serviços de lavagem

    // Pagamento
    public $paymentMethod = 'Dinheiro';
    public $discount = 0;
    public $discountType = 'fixed'; // fixed ou percentage
    public $receivedAmount = 0;
    public $notes = '';

    // Totais
    public $subtotal = 0;
    public $total = 0;
    public $change = 0;

    // Estados
    public $showClientModal = false;
    public $showServicesModal = false;
    public $isProcessing = false;

    // Configurações de serviços
    public $availableServices = [
        ['id' => 'lavagem_simples', 'name' => 'Lavagem Simples', 'price' => 15.00, 'duration' => 30],
        ['id' => 'lavagem_completa', 'name' => 'Lavagem Completa', 'price' => 25.00, 'duration' => 60],
        ['id' => 'enceramento', 'name' => 'Enceramento', 'price' => 35.00, 'duration' => 45],
        ['id' => 'lavagem_detalhada', 'name' => 'Lavagem Detalhada', 'price' => 50.00, 'duration' => 90],
        ['id' => 'lavagem_moto', 'name' => 'Lavagem Moto', 'price' => 12.00, 'duration' => 20],
    ];

    protected $listeners = [
        'clientSelected' => 'selectClient',
        'productScanned' => 'addProductByBarcode',
    ];

    public function mount()
    {
        $this->calculateTotals();
    }

    public function updatedSearch()
    {
        $this->dispatch('searchUpdated', $this->search);
    }

    public function updatedClientSearch()
    {
        // Reset client selection when searching
        if (strlen($this->clientSearch) < 3) {
            $this->selectedClient = null;
        }
    }

    public function updatedReceivedAmount()
    {
        $this->change = max(0, $this->receivedAmount - $this->total);
    }

    public function updatedDiscount()
    {
        $this->calculateTotals();
    }

    public function updatedDiscountType()
    {
        $this->calculateTotals();
    }

    // Produtos
    public function addToCart($productId, $quantity = 1)
    {
        $product = Product::find($productId);
        if (!$product) return;

        // Verificar estoque
        $inventory = Inventory::where('product_id', $productId)
            ->where('franchise_id', auth()->user()->franchise_id)
            ->first();

        if ($inventory && $inventory->quantity < $quantity) {
            session()->flash('error', 'Estoque insuficiente. Disponível: ' . $inventory->quantity);
            return;
        }

        $cartKey = 'product_' . $productId;

        if (isset($this->cart[$cartKey])) {
            $this->cart[$cartKey]['qty'] += $quantity;
        } else {
            $this->cart[$cartKey] = [
                'type' => 'product',
                'id' => $productId,
                'name' => $product->name,
                'price' => $product->price,
                'qty' => $quantity,
                'category' => $product->category,
                'sku' => $product->sku,
            ];
        }

        $this->calculateTotals();
        $this->dispatch('productAdded', $product->name);
    }

    public function addProductByBarcode($barcode)
    {
        $product = Product::where('sku', $barcode)->first();
        if ($product) {
            $this->addToCart($product->id);
        } else {
            session()->flash('error', 'Produto não encontrado: ' . $barcode);
        }
    }

    // Serviços
    public function addService($serviceId)
    {
        $service = collect($this->availableServices)->firstWhere('id', $serviceId);
        if (!$service) return;

        $cartKey = 'service_' . $serviceId;

        if (isset($this->cart[$cartKey])) {
            $this->cart[$cartKey]['qty']++;
        } else {
            $this->cart[$cartKey] = [
                'type' => 'service',
                'id' => $serviceId,
                'name' => $service['name'],
                'price' => $service['price'],
                'qty' => 1,
                'duration' => $service['duration'],
            ];
        }

        $this->calculateTotals();
        $this->showServicesModal = false;
    }

    public function updateQuantity($cartKey, $quantity)
    {
        if ($quantity <= 0) {
            $this->removeFromCart($cartKey);
            return;
        }

        if (isset($this->cart[$cartKey])) {
            // Se for produto, verificar estoque
            if ($this->cart[$cartKey]['type'] === 'product') {
                $inventory = Inventory::where('product_id', $this->cart[$cartKey]['id'])
                    ->where('franchise_id', auth()->user()->franchise_id)
                    ->first();

                if ($inventory && $inventory->quantity < $quantity) {
                    session()->flash('error', 'Estoque insuficiente. Disponível: ' . $inventory->quantity);
                    return;
                }
            }

            $this->cart[$cartKey]['qty'] = $quantity;
            $this->calculateTotals();
        }
    }

    public function removeFromCart($cartKey)
    {
        unset($this->cart[$cartKey]);
        $this->calculateTotals();
    }

    public function clearCart()
    {
        $this->cart = [];
        $this->services = [];
        $this->selectedClient = null;
        $this->discount = 0;
        $this->notes = '';
        $this->receivedAmount = 0;
        $this->calculateTotals();
    }

    // Cliente
    public function selectClient($clientId)
    {
        $this->selectedClient = Client::find($clientId);
        $this->showClientModal = false;
        $this->clientSearch = $this->selectedClient ? $this->selectedClient->name : '';
    }

    public function createQuickClient()
    {
        if (!$this->clientSearch) return;

        $client = Client::create([
            'name' => $this->clientSearch,
            'franchise_id' => auth()->user()->franchise_id,
        ]);

        $this->selectClient($client->id);
    }

    // Cálculos
    public function calculateTotals()
    {
        $this->subtotal = collect($this->cart)->sum(function($item) {
            return $item['price'] * $item['qty'];
        });

        $discountAmount = 0;
        if ($this->discount > 0) {
            $discountAmount = $this->discountType === 'percentage'
                ? ($this->subtotal * $this->discount / 100)
                : $this->discount;
        }

        $this->total = max(0, $this->subtotal - $discountAmount);
        $this->change = max(0, $this->receivedAmount - $this->total);
    }

    // Finalização
    public function checkout()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Carrinho vazio!');
            return;
        }

        $this->authorize('create', Sale::class);
        $this->isProcessing = true;

        DB::beginTransaction();
        try {
            // Criar venda
            $sale = Sale::create([
                'franchise_id' => auth()->user()->franchise_id,
                'user_id' => auth()->id(),
                'client_id' => $this->selectedClient ? $this->selectedClient->id : null,
                'total' => $this->total,
                'payment_method' => $this->paymentMethod,
                'discount' => $this->discountType === 'percentage'
                    ? ($this->subtotal * $this->discount / 100)
                    : $this->discount,
                'extra' => 0,
                'date' => now()->toDateString(),
                'receipt_number' => $this->generateReceiptNumber(),
                'notes' => $this->notes,
            ]);

            // Adicionar itens
            foreach ($this->cart as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['type'] === 'product' ? $item['id'] : null,
                    'service_id' => $item['type'] === 'service' ? $item['id'] : null,
                    'qty' => $item['qty'],
                    'unit_price' => $item['price'],
                    'subtotal' => $item['price'] * $item['qty'],
                    'type' => $item['type'],
                    'description' => $item['name'],
                ]);

                // Atualizar estoque para produtos
                if ($item['type'] === 'product') {
                    $inventory = Inventory::firstOrCreate(
                        [
                            'product_id' => $item['id'],
                            'franchise_id' => auth()->user()->franchise_id
                        ],
                        ['quantity' => 0]
                    );
                    $inventory->decrement('quantity', $item['qty']);
                }
            }

            DB::commit();

            // Reset
            $this->clearCart();
            $this->isProcessing = false;

            session()->flash('success', 'Venda registrada com sucesso! Recibo: #' . $sale->receipt_number);

            // Dispatch event para impressão (se necessário)
            $this->dispatch('saleCompleted', $sale->id);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->isProcessing = false;
            session()->flash('error', 'Erro ao processar venda: ' . $e->getMessage());
        }
    }

    private function generateReceiptNumber()
    {
        $today = now()->format('Ymd');
        $lastSale = Sale::whereDate('created_at', now()->toDateString())
            ->where('franchise_id', auth()->user()->franchise_id)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastSale ? (intval(substr($lastSale->receipt_number, -4)) + 1) : 1;

        return $today . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function render()
    {
        // Buscar produtos
        $products = Product::query()
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('sku', 'like', "%{$this->search}%");
                });
            })
            ->when($this->selectedCategory !== 'all', function($query) {
                $query->where('category', $this->selectedCategory);
            })
            ->orderBy('name')
            ->limit(20)
            ->get();

        // Buscar clientes
        $clients = [];
        if ($this->clientSearch && strlen($this->clientSearch) >= 3) {
            $clients = Client::where('franchise_id', auth()->user()->franchise_id)
                ->where(function($q) {
                    $q->where('name', 'like', "%{$this->clientSearch}%")
                        ->orWhere('phone', 'like', "%{$this->clientSearch}%")
                        ->orWhere('document', 'like', "%{$this->clientSearch}%");
                })
                ->limit(10)
                ->get();
        }

        // Categorias disponíveis
        $categories = Product::distinct()->pluck('category')->filter();

        return view('livewire.pdv.enhanced-checkout', [
            'products' => $products,
            'clients' => $clients,
            'categories' => $categories,
        ]);
    }
}
