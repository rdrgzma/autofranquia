<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Product;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true; // lista acessível; filtrar por query scope se necessário
    }

    public function view(User $user, Product $product)
    {
        // produtos podem ser globais ou vinculados via inventory/franchise. Ajuste conforme seu model.
        return true;
    }

    public function create(User $user)
    {
        return in_array($user->role, ['super_admin', 'franchise_admin']);
    }

    public function update(User $user, Product $product)
    {
        // super_admin já liberado pelo Gate::before
        // franquia pode editar se produto estiver vinculado ao inventário da franquia (alternativa: campo franchise_id)
        if ($user->role === 'franchise_admin') {
            // se existir relacionamento product->inventories com franquia, verifique lá:
            return $product->inventories()->where('franchise_id', $user->franchise_id)->exists();
        }
        return false;
    }

    public function delete(User $user, Product $product)
    {
        return $user->role === 'super_admin';
    }
}
