<?php

namespace App\Policies;

use App\Models\PortfolioDocument;
use App\Models\User;

class PortfolioDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'analyst', 'viewer', 'coordinator', 'collector', 'auditor']);
    }

    public function view(User $user, PortfolioDocument $doc): bool
    {
        return $user->hasAnyRole(['admin', 'analyst', 'viewer', 'coordinator', 'collector', 'auditor']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'analyst', 'coordinator']);
    }

    public function update(User $user, PortfolioDocument $doc): bool
    {
        return $user->hasAnyRole(['admin', 'analyst', 'coordinator']);
    }

    public function delete(User $user, PortfolioDocument $doc): bool
    {
        return $user->hasRole('admin');
    }

    public function restore(User $user, PortfolioDocument $doc): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, PortfolioDocument $doc): bool
    {
        return $user->hasRole('admin');
    }
}
