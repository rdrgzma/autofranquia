<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\SaleItem;
use App\Models\Sale;
use App\Models\Product;

class SaleItemFactory extends Factory
{
    protected $model = SaleItem::class;

    public function definition()
    {
        $sale = Sale::inRandomOrder()->first();
        $product = Product::inRandomOrder()->first();

        return [
            'sale_id' => $sale->id ?? null,
            'product_id' => $product->id ?? null,
            'qty' => $this->faker->numberBetween(1,3),
            'unit_price' => $product->price ?? $this->faker->randomFloat(2,10,200),
            'subtotal' => function (array $attrs) {
                return ($attrs['unit_price'] ?? 0) * ($attrs['qty'] ?? 1);
            },
        ];
    }
}
