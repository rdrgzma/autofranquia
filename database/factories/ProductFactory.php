<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        $category = $this->faker->randomElement(['Peças', 'Serviços']);
        $sku = strtoupper($this->faker->bothify('PROD-####')) . '-' . substr(md5(uniqid()),0,4);
        return [
            'name' => $this->faker->words(3, true),
            'sku' => $sku,
            'category' => $category,
            'price' => $this->faker->randomFloat(2, 5, 1500),
            'default_image' => null,
        ];
    }
}
