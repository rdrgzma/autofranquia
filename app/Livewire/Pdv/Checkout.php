<?php

namespace App\Livewire\Pdv;

use Livewire\Component;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Checkout extends Component
{
    use AuthorizesRequests;

    public $search = '';
    public $cart = [];
    public $paymentMethod = 'Dinheiro';
    public $discount = 0;
    public $total = 0;

    public function updatedSearch()
    {
        $this->dispatch('searchUpdated', $this->search);
    }

    public function addToCart($id)
    {
        $product = Product::find($id);
        if (!$product) return;

        if (isset($this->cart[$id])) {
            $this->cart[$id]['qty']++;
        } else {
            $this->cart[$id] = [
                'name' => $product->name,
                'price' => $product->price,
                'qty' => 1
            ];
        }
        $this->calculateTotal();
    }

    public function removeFromCart($id)
    {
        unset($this->cart[$id]);
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $subtotal = collect($this->cart)->sum(fn($item) => $item['price'] * $item['qty']);
        $this->total = max(0, $subtotal - $this->discount);
    }

    public function checkout()
    {
        $user = auth()->user();
        if (!$user) return;

        $this->authorize('create', Sale::class);

        DB::beginTransaction();
        try {
            $sale = Sale::create([
                'franchise_id' => $user->franchise_id,
                'user_id' => $user->id,
                'total' => $this->total,
                'payment_method' => $this->paymentMethod,
                'discount' => $this->discount,
                'extra' => 0,
                'date' => now()->toDateString(),
                'receipt_number' => (Sale::max('id') ?? 0) + 1,
            ]);

            foreach ($this->cart as $id => $item) {
                $product = Product::find($id);
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $id,
                    'qty' => $item['qty'],
                    'unit_price' => $item['price'],
                    'subtotal' => $item['price'] * $item['qty'],
                ]);

                if ($product->category !== 'Serviços') {
                    $inv = Inventory::firstOrCreate(['product_id' => $id, 'franchise_id' => $user->franchise_id], ['quantity' => 0]);
                    $inv->decrement('quantity', $item['qty']);
                }
            }

            DB::commit();
            $this->reset(['cart', 'total', 'discount']);
            session()->flash('message', 'Venda registrada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erro ao registrar venda: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $products = Product::where('name', 'like', "%{$this->search}%")->limit(10)->get();
        return view('livewire.pdv.checkout', compact('products'));
    }
}
