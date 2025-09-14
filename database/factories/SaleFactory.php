<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Sale;
use App\Models\Franchise;
use App\Models\User;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\Inventory;
use App\Models\FinancialTransaction;

class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition()
    {
        $franchise = Franchise::inRandomOrder()->first();
        $user = User::where('franchise_id', $franchise->id)->inRandomOrder()->first() ?? User::inRandomOrder()->first();

        return [
            'franchise_id' => $franchise->id ?? null,
            'user_id' => $user->id ?? 1,
            'client_id' => null,
            'total' => 0,
            'payment_method' => $this->faker->randomElement(['Dinheiro','Cartão','Pix']),
            'discount' => 0,
            'extra' => 0,
            'date' => $this->faker->dateTimeBetween('-60 days','now')->format('Y-m-d'),
            'receipt_number' => Sale::max('id') + 1,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Sale $sale) {
            $products = Product::inRandomOrder()->limit(rand(1,4))->get();

            $total = 0;
            foreach ($products as $product) {
                $qty = rand(1,3);
                $subtotal = $qty * $product->price;
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                ]);

                if ($product->category !== 'Serviços') {
                    $inv = Inventory::firstOrCreate(['product_id' => $product->id, 'franchise_id' => $sale->franchise_id], ['quantity' => 0]);
                    $take = min($inv->quantity, $qty);
                    $inv->decrement('quantity', $take);
                }

                $total += $subtotal;
            }

            $discount = rand(0,500) / 100.0;
            $total_after = max(0, $total - $discount);
            $sale->update(['total' => $total_after, 'discount' => $discount]);

            FinancialTransaction::create([
                'franchise_id' => $sale->franchise_id,
                'type' => 'entrada',
                'value' => $total_after,
                'description' => 'Venda PDV - factory',
                'date' => $sale->date,
                'created_by' => $sale->user_id,
            ]);
        });
    }
}
