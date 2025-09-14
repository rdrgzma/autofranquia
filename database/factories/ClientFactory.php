<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Client;
use App\Models\Franchise;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition()
    {
        $isCompany = $this->faker->boolean(10);
        $franchise = Franchise::inRandomOrder()->first();

        return [
            'name' => $isCompany ? $this->faker->company : $this->faker->name,
            'document' => $isCompany ? $this->faker->numerify('##.###.###/0001-##') : $this->faker->numerify('###.###.###-##'),
            'document_type' => $isCompany ? 'CNPJ' : 'CPF',
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'vehicle' => $isCompany ? null : $this->faker->bothify('Model ####'),
            'address' => [
                'street' => $this->faker->streetName,
                'number' => $this->faker->buildingNumber,
                'city' => $this->faker->city,
                'zip' => $this->faker->postcode,
            ],
            'franchise_id' => $franchise->id ?? null,
        ];
    }
}
