<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Franchise;

class InventoryFactory extends Factory
{
    protected $model = Inventory::class;

    public function definition()
    {
        $product = Product::inRandomOrder()->first();
        $franchise = Franchise::inRandomOrder()->first();

        return [
            'product_id' => $product->id ?? null,
            'franchise_id' => $franchise->id ?? null,
            'quantity' => $this->faker->numberBetween(0, 200),
            'location' => 'Estoque ' . $this->faker->randomElement(['A1','A2','B1','B2','C1']),
            'min_stock' => $this->faker->numberBetween(0,10),
        ];
    }
}
