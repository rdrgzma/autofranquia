<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Franchise;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        $central = Franchise::where('name', 'Franquia Central')->first();
        $norte   = Franchise::where('name', 'Franquia Norte')->first();

        User::firstOrCreate(
            ['email' => 'admin@fabricadanet.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'role' => 'super_admin',
                'franchise_id' => null,
            ]
        );

        User::firstOrCreate(
            ['email' => 'franquiacentral@admin.com'],
            [
                'name' => 'Admin Franquia Central',
                'password' => bcrypt('password'),
                'role' => 'franchise_admin',
                'franchise_id' => $central->id,
            ]
        );

        User::firstOrCreate(
            ['email' => 'franquianorte@admin.com'],
            [
                'name' => 'Admin Franquia Norte',
                'password' => bcrypt('password'),
                'role' => 'franchise_admin',
                'franchise_id' => $norte->id,
            ]
        );
    }
}
