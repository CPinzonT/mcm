<?php

namespace App\Policies;

use App\Models\PortfolioLoad;
use App\Models\User;

class PortfolioLoadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'analyst', 'analista']);
    }

    public function view(User $user, PortfolioLoad $portfolioLoad): bool
    {
        return $user->hasAnyRole(['admin', 'analyst', 'analista']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'analyst', 'analista']);
    }

    public function update(User $user, PortfolioLoad $portfolioLoad): bool
    {
        return $user->hasAnyRole(['admin', 'analyst', 'analista']);
    }

    public function delete(User $user, PortfolioLoad $portfolioLoad): bool
    {
        return $user->hasRole('admin');
    }
}
