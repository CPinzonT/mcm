<?php

namespace App\Policies;

use App\Models\SalesLoad;
use App\Models\User;

class SalesLoadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view sales') || $user->hasAnyRole(['admin', 'analyst', 'analista']);
    }

    public function view(User $user, SalesLoad $salesLoad): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('upload sales loads') || $user->hasAnyRole(['admin', 'analyst', 'analista']);
    }

    public function update(User $user, SalesLoad $salesLoad): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, SalesLoad $salesLoad): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if (! $user->hasAnyRole(['analyst', 'analista'])) {
            return false;
        }

        return in_array($salesLoad->status, ['pending', 'rejected', 'failed'], true);
    }
}
