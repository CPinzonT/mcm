<?php

namespace App\Policies;

use App\Models\BudgetLoad;
use App\Models\User;

class BudgetLoadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'analyst', 'analista']);
    }

    public function view(User $user, BudgetLoad $budgetLoad): bool
    {
        return $user->hasAnyRole(['admin', 'analyst', 'analista']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'analyst', 'analista']);
    }

    public function delete(User $user, BudgetLoad $budgetLoad): bool
    {
        return $user->hasRole('admin');
    }
}
