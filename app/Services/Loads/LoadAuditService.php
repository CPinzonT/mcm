<?php

namespace App\Services\Loads;

use App\Models\LoadAudit;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class LoadAuditService
{
    public function record(
        Model $auditable,
        string $module,
        string $action,
        string $description,
        User|int|null $user = null,
        array $payload = [],
    ): void {
        $auditable->audits()->create([
            'module' => $module,
            'action' => $action,
            'description' => $description,
            'payload' => $payload ?: null,
            'user_id' => $user instanceof User ? $user->id : $user,
        ]);
    }
}
