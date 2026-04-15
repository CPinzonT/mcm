<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'analyst', 'viewer', 'coordinator', 'collector', 'auditor']);
    }

    public function view(User $user, Client $client): bool
    {
        return $user->hasAnyRole(['admin', 'analyst', 'viewer', 'coordinator', 'collector', 'auditor']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'analyst', 'coordinator']);
    }

    public function update(User $user, Client $client): bool
    {
        return $user->hasAnyRole(['admin', 'analyst', 'coordinator']);
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->hasRole('admin');
    }

    public function restore(User $user, Client $client): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, Client $client): bool
    {
        return $user->hasRole('admin');
    }
}
