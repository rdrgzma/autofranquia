<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\FinancialTransaction;
use App\Models\Franchise;
use App\Models\User;

class FinancialTransactionFactory extends Factory
{
    protected $model = FinancialTransaction::class;

    public function definition()
    {
        $franchise = Franchise::inRandomOrder()->first();
        $user = User::where('franchise_id', $franchise->id)->inRandomOrder()->first() ?? User::inRandomOrder()->first();

        return [
            'franchise_id' => $franchise->id ?? null,
            'type' => $this->faker->randomElement(['entrada','saida']),
            'value' => $this->faker->randomFloat(2, 10, 2000),
            'description' => $this->faker->sentence(4),
            'date' => $this->faker->dateTimeBetween('-60 days','now')->format('Y-m-d'),
            'created_by' => $user->id ?? 1,
        ];
    }
}
