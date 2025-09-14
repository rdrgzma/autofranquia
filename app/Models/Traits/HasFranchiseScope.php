<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait HasFranchiseScope
{
    /**
     * Aplica filtro por franquia baseado no usuário autenticado.
     * Se o usuário for super_admin, não aplica filtro.
     *
     * Uso: Model::forUser()->get();
     */
    public function scopeForUser(Builder $query, $user = null)
    {
        $user = $user ?? Auth::user();

        if (! $user) {
            return $query;
        }

        if ($user->role === 'super_admin') {
            return $query;
        }

        return $query->where('franchise_id', $user->franchise_id);
    }
}
