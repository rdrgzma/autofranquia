<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Client;
use App\Models\FinancialTransaction;
use App\Policies\ProductPolicy;
use App\Policies\SalePolicy;
use App\Policies\ClientPolicy;
use App\Policies\FinancialTransactionPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Product::class => ProductPolicy::class,
        Sale::class => SalePolicy::class,
        Client::class => ClientPolicy::class,
        FinancialTransaction::class => FinancialTransactionPolicy::class,
    ];

    public function boot()
    {
        $this->registerPolicies();

        // super_admin bypass
        Gate::before(function ($user, $ability) {
            return $user->role === 'super_admin' ? true : null;
        });
    }
}
