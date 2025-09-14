<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Franchise;

class FranchiseFactory extends Factory
{
    protected $model = Franchise::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company . ' Franquia',
            'cnpj' => $this->faker->numerify('##.###.###/0001-##'),
            'address' => $this->faker->streetAddress,
            'phone' => $this->faker->phoneNumber,
        ];
    }
}
