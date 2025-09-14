<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Client;
use App\Models\Sale;
use App\Models\FinancialTransaction;

class LargeDataSeeder extends Seeder
{
    public function run()
    {
        Product::factory()->count(200)->create();
        Client::factory()->count(500)->create();

        Sale::factory()->count(1000)->create();

        FinancialTransaction::factory()->count(500)->create();
    }
}
