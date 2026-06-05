<?php

namespace App\Policies;

use App\Models\CollectionLoad;
use App\Models\User;

class CollectionLoadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'analyst', 'analista']);
    }

    public function view(User $user, CollectionLoad $collectionLoad): bool
    {
        return $user->hasAnyRole(['admin', 'analyst', 'analista']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'analyst', 'analista']);
    }

    public function update(User $user, CollectionLoad $collectionLoad): bool
    {
        return $user->hasAnyRole(['admin', 'analyst', 'analista']);
    }

    public function delete(User $user, CollectionLoad $collectionLoad): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if (! $user->hasAnyRole(['analyst', 'analista'])) {
            return false;
        }

        return in_array($collectionLoad->status, ['pending', 'rejected', 'failed', 'cancelled'], true)
            || ! $collectionLoad->is_active;
    }
}
