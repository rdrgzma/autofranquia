<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Franchise;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        $franchise = Franchise::inRandomOrder()->first();

        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => $this->faker->randomElement(['collaborator','franchise_admin']),
            'franchise_id' => $franchise->id ?? null,
        ];
    }

    public function superAdmin()
    {
        return $this->state(fn(array $attrs) => ['role' => 'super_admin', 'franchise_id' => null]);
    }
}
