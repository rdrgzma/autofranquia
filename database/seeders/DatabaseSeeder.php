<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            FranchisesTableSeeder::class,
            UsersTableSeeder::class,
            ProductsTableSeeder::class, // optional, if you have a seeded products file
            AdditionalProductsSeeder::class,
            ClientsTableSeeder::class,
            SalesTableSeeder::class,
            FinancialTransactionsTableSeeder::class,
            LargeDataSeeder::class,
        ]);
    }
}
