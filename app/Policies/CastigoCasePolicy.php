<?php

namespace App\Policies;

use App\Models\CastigoCase;
use App\Models\User;

class CastigoCasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'analyst', 'coordinator', 'auditor', 'castigo_manager']);
    }

    public function view(User $user, CastigoCase $case): bool
    {
        return $user->hasAnyRole(['admin', 'analyst', 'coordinator', 'auditor', 'castigo_manager']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'analyst', 'coordinator', 'castigo_manager']);
    }

    public function update(User $user, CastigoCase $case): bool
    {
        if ($case->status === 'closed' || $case->status === 'submitted_dian') {
            return $user->hasRole('admin');
        }

        return $user->hasAnyRole(['admin', 'analyst', 'coordinator', 'castigo_manager']);
    }

    public function approve(User $user, CastigoCase $case): bool
    {
        return $user->hasAnyRole(['admin', 'coordinator']);
    }

    public function delete(User $user, CastigoCase $case): bool
    {
        return $user->hasRole('admin') && $case->status === 'draft';
    }

    public function restore(User $user, CastigoCase $case): bool
    {
        return $user->hasRole('admin');
    }

    public function forceDelete(User $user, CastigoCase $case): bool
    {
        return $user->hasRole('admin');
    }
}
