<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Franchise;

class ProductsTableSeeder extends Seeder
{
    public function run()
    {
        $central = Franchise::where('name', 'Franquia Central')->first();
        $norte   = Franchise::where('name', 'Franquia Norte')->first();

        $filtro = Product::firstOrCreate(
            ['sku' => 'FILTROOLEO1'],
            [
                'name' => 'Filtro de Óleo',
                'category' => 'Peças',
                'price' => 29.90,
                'default_image' => null,
            ]
        );

        $balanco = Product::firstOrCreate(
            ['sku' => 'BALANC10'],
            [
                'name' => 'Alinhamento e Balanceamento',
                'category' => 'Serviços',
                'price' => 120.00,
                'default_image' => null,
            ]
        );

        Inventory::updateOrCreate(
            ['product_id' => $filtro->id, 'franchise_id' => $central->id],
            ['quantity' => 40, 'location' => 'Estoque A2', 'min_stock' => 5]
        );

        Inventory::updateOrCreate(
            ['product_id' => $filtro->id, 'franchise_id' => $norte->id],
            ['quantity' => 25, 'location' => 'Estoque B1', 'min_stock' => 5]
        );
    }
}
