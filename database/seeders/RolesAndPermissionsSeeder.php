<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Clientes
            'view clients', 'create clients', 'edit clients', 'delete clients',
            // Cartera
            'view portfolio', 'create portfolio', 'edit portfolio', 'delete portfolio',
            'upload portfolio loads',
            // Recaudos
            'view collections', 'upload collection loads',
            // Gestión
            'view management logs', 'create management logs', 'edit management logs',
            // Reportes
            'view reports', 'generate reports', 'manage report templates',
            'export reports',
            // Castigos DIAN
            'view castigo cases', 'create castigo cases', 'edit castigo cases',
            'approve castigo cases', 'delete castigo cases',
            'upload support documents', 'download support documents',
            // Dashboards
            'view executive dashboard', 'view operative dashboard',
            // Configuración
            'manage system config', 'manage roles',
            'manage advisors', 'manage branding',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $roles = [
            'admin' => $permissions,
            'coordinator' => [
                'view clients', 'create clients', 'edit clients',
                'view portfolio', 'create portfolio', 'edit portfolio', 'upload portfolio loads',
                'view collections', 'upload collection loads',
                'view management logs', 'create management logs', 'edit management logs',
                'view reports', 'generate reports', 'manage report templates', 'export reports',
                'view castigo cases', 'create castigo cases', 'edit castigo cases', 'approve castigo cases',
                'upload support documents', 'download support documents',
                'view executive dashboard', 'view operative dashboard',
                'manage advisors',
            ],
            'analyst' => [
                'view clients', 'create clients', 'edit clients',
                'view portfolio', 'create portfolio', 'edit portfolio', 'upload portfolio loads',
                'view collections', 'upload collection loads',
                'view management logs', 'create management logs', 'edit management logs',
                'view reports', 'generate reports', 'export reports',
                'view castigo cases', 'create castigo cases', 'edit castigo cases',
                'upload support documents', 'download support documents',
                'view executive dashboard', 'view operative dashboard',
            ],
            'analista' => [
                'view clients', 'create clients', 'edit clients',
                'view portfolio', 'create portfolio', 'edit portfolio', 'upload portfolio loads',
                'view collections', 'upload collection loads',
                'view management logs', 'create management logs', 'edit management logs',
                'view reports', 'generate reports', 'export reports',
                'view castigo cases', 'create castigo cases', 'edit castigo cases',
                'upload support documents', 'download support documents',
                'view executive dashboard', 'view operative dashboard',
            ],
            'collector' => [
                'view clients', 'view portfolio',
                'view collections',
                'view management logs', 'create management logs',
                'view reports', 'export reports',
                'view operative dashboard',
            ],
            'viewer' => [
                'view clients', 'view portfolio', 'view collections',
                'view management logs', 'view reports',
                'view executive dashboard', 'view operative dashboard',
            ],
            'auditor' => [
                'view clients', 'view portfolio', 'view collections',
                'view management logs', 'view reports', 'export reports',
                'view castigo cases', 'download support documents',
                'view executive dashboard', 'view operative dashboard',
            ],
            'castigo_manager' => [
                'view clients', 'view portfolio',
                'view castigo cases', 'create castigo cases', 'edit castigo cases',
                'upload support documents', 'download support documents',
                'view operative dashboard',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }
    }
}
