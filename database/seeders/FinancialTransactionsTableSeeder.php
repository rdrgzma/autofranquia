<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FinancialTransaction;
use App\Models\Franchise;
use Carbon\Carbon;

class FinancialTransactionsTableSeeder extends Seeder
{
    public function run()
    {
        $central = Franchise::where('name', 'Franquia Central')->first();
        $norte   = Franchise::where('name', 'Franquia Norte')->first();

        $today = Carbon::now()->toDateString();

        $tx = [
            ['franchise_id' => $central->id, 'type' => 'saida', 'value' => 250.00, 'description' => 'Compra de materiais - seed', 'date' => $today, 'created_by' => \App\Models\User::where('franchise_id',$central->id)->first()->id ?? 1],
            ['franchise_id' => $central->id, 'type' => 'entrada', 'value' => 1500.00, 'description' => 'Serviço contratado X - seed', 'date' => $today, 'created_by' => \App\Models\User::where('franchise_id',$central->id)->first()->id ?? 1],
            ['franchise_id' => $norte->id, 'type' => 'saida', 'value' => 420.50, 'description' => 'Pagamento fornecedor - seed', 'date' => $today, 'created_by' => \App\Models\User::where('franchise_id',$norte->id)->first()->id ?? 2],
        ];

        foreach ($tx as $t) {
            FinancialTransaction::create($t);
        }
    }
}
