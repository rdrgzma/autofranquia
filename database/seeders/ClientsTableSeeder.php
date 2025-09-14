<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\Franchise;

class ClientsTableSeeder extends Seeder
{
    public function run()
    {
        $central = Franchise::where('name', 'Franquia Central')->first();
        $norte   = Franchise::where('name', 'Franquia Norte')->first();

        $clients = [
            ['name' => 'João Silva', 'document' => '123.456.789-00', 'document_type'=>'CPF', 'phone'=>'(51) 99911-1111', 'email'=>'joao@example.com', 'vehicle'=>'Gol 2012', 'franchise_id' => $central->id],
            ['name' => 'Maria Oliveira', 'document' => '987.654.321-00', 'document_type'=>'CPF', 'phone'=>'(51) 99922-2222', 'email'=>'maria@example.com', 'vehicle'=>'HB20 2018', 'franchise_id' => $central->id],
            ['name' => 'Oficina AutoX', 'document' => '12.345.678/0001-90', 'document_type'=>'CNPJ', 'phone'=>'(51) 3333-3333', 'email'=>'contato@autox.com', 'vehicle'=>null, 'franchise_id' => $central->id],
            ['name' => 'Carlos Pereira', 'document' => '321.654.987-00', 'document_type'=>'CPF', 'phone'=>'(51) 99933-3333', 'email'=>'carlos@example.com', 'vehicle'=>'Fiesta 2015', 'franchise_id' => $norte->id],
            ['name' => 'Luciana Ramos', 'document' => '111.222.333-44', 'document_type'=>'CPF', 'phone'=>'(51) 99944-4444', 'email'=>'luciana@example.com', 'vehicle'=>'Civic 2020', 'franchise_id' => $norte->id],
            ['name' => 'Frota Comercial LTDA', 'document' => '98.765.432/0001-11', 'document_type'=>'CNPJ', 'phone'=>'(51) 3334-3334', 'email'=>'frota@example.com', 'vehicle'=>null, 'franchise_id' => $norte->id],
        ];

        foreach ($clients as $c) {
            Client::create($c);
        }
    }
}
