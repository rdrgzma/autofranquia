<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Sale;
use Illuminate\Auth\Access\HandlesAuthorization;

class SalePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Sale $sale)
    {
        return $user->role === 'super_admin' || $sale->franchise_id === $user->franchise_id;
    }

    public function create(User $user)
    {
        return in_array($user->role, ['super_admin','franchise_admin','collaborator']);
    }

    public function update(User $user, Sale $sale)
    {
        if ($user->role === 'franchise_admin') {
            return $sale->franchise_id === $user->franchise_id;
        }
        return false;
    }

    public function delete(User $user, Sale $sale)
    {
        return $user->role === 'super_admin';
    }
}
