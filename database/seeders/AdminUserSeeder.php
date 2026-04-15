<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => (string) env('APP_ADMIN_EMAIL', 'admin@cartera.local')],
            [
                'name'     => (string) env('APP_ADMIN_NAME', 'Administrador'),
                'password' => Hash::make((string) env('APP_ADMIN_PASSWORD', 'Admin2026#')),
            ]
        );

        $admin->syncRoles(['admin']);

        $analyst = User::updateOrCreate(
            ['email' => (string) env('APP_ANALYST_EMAIL', 'analista@cartera.local')],
            [
                'name'     => (string) env('APP_ANALYST_NAME', 'Analista Demo'),
                'password' => Hash::make((string) env('APP_ANALYST_PASSWORD', 'Analista2026#')),
            ]
        );

        $analyst->syncRoles([(string) env('APP_ANALYST_ROLE', 'analyst')]);
    }
}
