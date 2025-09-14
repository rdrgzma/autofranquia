<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Client;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClientPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Client $client)
    {
        return $user->role === 'super_admin' || $client->franchise_id === $user->franchise_id;
    }

    public function create(User $user)
    {
        return in_array($user->role, ['super_admin','franchise_admin','collaborator']);
    }

    public function update(User $user, Client $client)
    {
        return $user->role === 'super_admin' || $client->franchise_id === $user->franchise_id;
    }

    public function delete(User $user, Client $client)
    {
        return $user->role === 'super_admin' || ($client->franchise_id === $user->franchise_id && $user->role === 'franchise_admin');
    }
}
