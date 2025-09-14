<?php

namespace App\Policies;

use App\Models\User;
use App\Models\FinancialTransaction;
use Illuminate\Auth\Access\HandlesAuthorization;

class FinancialTransactionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, FinancialTransaction $tx)
    {
        return $user->role === 'super_admin' || $tx->franchise_id === $user->franchise_id;
    }

    public function create(User $user)
    {
        return in_array($user->role, ['super_admin','franchise_admin']);
    }

    public function update(User $user, FinancialTransaction $tx)
    {
        return $user->role === 'super_admin' || ($tx->franchise_id === $user->franchise_id && $user->role === 'franchise_admin');
    }

    public function delete(User $user, FinancialTransaction $tx)
    {
        return $user->role === 'super_admin';
    }
}
