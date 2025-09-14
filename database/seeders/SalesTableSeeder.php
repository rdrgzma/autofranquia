<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Franchise;
use App\Models\Client;
use App\Models\FinancialTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SalesTableSeeder extends Seeder
{
    public function run()
    {
        $central = Franchise::where('name', 'Franquia Central')->first();
        $norte   = Franchise::where('name', 'Franquia Norte')->first();

        // Garantir existência dos produtos usados no seed — cria fallback se não existir
        $oleo = Product::firstOrCreate(
            ['sku' => 'OLEO5W30'],
            ['name' => 'Óleo 5W30', 'category' => 'Peças', 'price' => 120.00]
        );

        $troca = Product::firstOrCreate(
            ['sku' => 'TROCAOLEO'],
            ['name' => 'Troca de Óleo', 'category' => 'Serviços', 'price' => 80.00]
        );

        $filtro = Product::firstOrCreate(
            ['sku' => 'FILTROOLEO1'],
            ['name' => 'Filtro de Óleo', 'category' => 'Peças', 'price' => 29.90]
        );

        $clientCentral = Client::where('franchise_id', $central->id)->first();
        $clientNorte   = Client::where('franchise_id', $norte->id)->first();

        $now = Carbon::now();

        DB::beginTransaction();
        try {
            // Venda 1 - Franquia Central
            $sale1 = Sale::create([
                'franchise_id' => $central->id,
                'user_id' => \App\Models\User::where('role','franchise_admin')->where('franchise_id', $central->id)->first()->id ?? 1,
                'client_id' => $clientCentral->id ?? null,
                'total' => 0,
                'payment_method' => 'Dinheiro',
                'discount' => 0,
                'extra' => 0,
                'date' => $now->subDays(3)->toDateString(),
                'receipt_number' => (Sale::max('id') ?? 0) + 1,
            ]);

            $items1 = [
                ['product' => $oleo, 'qty' => 2, 'unit_price' => $oleo->price],
                ['product' => $troca, 'qty' => 1, 'unit_price' => $troca->price],
            ];

            $total1 = 0;
            foreach ($items1 as $it) {
                if (empty($it['product'])) continue;

                SaleItem::create([
                    'sale_id' => $sale1->id,
                    'product_id' => $it['product']->id,
                    'qty' => $it['qty'],
                    'unit_price' => $it['unit_price'],
                    'subtotal' => $it['qty'] * $it['unit_price'],
                ]);

                $total1 += $it['qty'] * $it['unit_price'];

                if ($it['product']->category !== 'Serviços') {
                    $inv = Inventory::firstOrCreate(['product_id' => $it['product']->id, 'franchise_id' => $central->id], ['quantity' => 0]);
                    $inv->decrement('quantity', $it['qty']);
                }
            }

            $sale1->update(['total' => $total1]);

            FinancialTransaction::create([
                'franchise_id' => $central->id,
                'type' => 'entrada',
                'value' => $total1,
                'description' => 'Venda PDV (Dinheiro) - seed',
                'date' => $sale1->date,
                'created_by' => $sale1->user_id,
            ]);

            // Venda 2 - Franquia Norte
            $sale2 = Sale::create([
                'franchise_id' => $norte->id,
                'user_id' => \App\Models\User::where('role','franchise_admin')->where('franchise_id', $norte->id)->first()->id ?? 2,
                'client_id' => $clientNorte->id ?? null,
                'total' => 0,
                'payment_method' => 'Cartão',
                'discount' => 5.00,
                'extra' => 0,
                'date' => $now->subDays(1)->toDateString(),
                'receipt_number' => (Sale::max('id') ?? 0) + 1,
            ]);

            $items2 = [
                ['product' => $filtro, 'qty' => 1, 'unit_price' => $filtro->price],
                ['product' => $troca, 'qty' => 1, 'unit_price' => $troca->price],
            ];

            $total2 = 0;
            foreach ($items2 as $it) {
                if (empty($it['product'])) continue;

                SaleItem::create([
                    'sale_id' => $sale2->id,
                    'product_id' => $it['product']->id,
                    'qty' => $it['qty'],
                    'unit_price' => $it['unit_price'],
                    'subtotal' => $it['qty'] * $it['unit_price'],
                ]);

                $total2 += $it['qty'] * $it['unit_price'];

                if ($it['product']->category !== 'Serviços') {
                    $inv = Inventory::firstOrCreate(['product_id' => $it['product']->id, 'franchise_id' => $norte->id], ['quantity' => 0]);
                    $inv->decrement('quantity', $it['qty']);
                }
            }

            $total2_after = max(0, $total2 - ($sale2->discount ?? 0));
            $sale2->update(['total' => $total2_after]);

            FinancialTransaction::create([
                'franchise_id' => $norte->id,
                'type' => 'entrada',
                'value' => $total2_after,
                'description' => 'Venda PDV (Cartão) - seed',
                'date' => $sale2->date,
                'created_by' => $sale2->user_id,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
