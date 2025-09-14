<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Franchise;

class FranchisesTableSeeder extends Seeder
{
    public function run()
    {
        Franchise::firstOrCreate(
            ['name' => 'Franquia Central'],
            ['cnpj' => '00.000.000/0001-00', 'address' => 'Av. Central, 1000']
        );

        Franchise::firstOrCreate(
            ['name' => 'Franquia Norte'],
            ['cnpj' => '11.111.111/0001-11', 'address' => 'Rua Norte, 500']
        );
    }
}
