<?php

namespace App\Filament\Pages;

use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleManagementPage extends Page
{
    protected string $view = 'filament.pages.role-management-page';

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-shield-check';
    protected static string|\UnitEnum|null   $navigationGroup = 'Configuración';
    protected static ?string $navigationLabel = 'Gestión avanzada de roles';
    protected static ?string $title           = 'Gestión Avanzada de Roles';
    protected static ?int    $navigationSort  = 4;

    public ?int  $selectedRoleId  = null;
    public array $rolePermissions = [];
    public string $roleSearch = '';
    public string $permissionSearch = '';
    public string $newRoleName = '';
    public $cloneRoleId = null;
    public bool $confirmingDeleteRole = false;
    public ?int $confirmingInlineDeleteRoleId = null;

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return null;
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->can('manage roles') ?? false;
    }

    #[Computed]
    public function roles()
    {
        return Role::withCount(['permissions', 'users'])->orderBy('name')->get();
    }

    #[Computed]
    public function filteredRoles(): Collection
    {
        $search = mb_strtolower(trim($this->roleSearch));

        if ($search === '') {
            return $this->roles;
        }

        return $this->roles->filter(
            fn (Role $role): bool => str_contains(mb_strtolower($role->name), $search)
        )->values();
    }

    #[Computed]
    public function permissions(): array
    {
        $moduleMap = [
            'Cartera' => [
                'create portfolio', 'delete portfolio', 'edit portfolio',
                'view portfolio', 'download support documents',
            ],
            'Clientes' => [
                'create clients', 'delete clients', 'edit clients', 'view clients',
            ],
            'Recaudos' => [
                'upload collection loads', 'view collections',
            ],
            'Cargas' => [
                'upload portfolio loads',
            ],
            'Gestión de cartera' => [
                'create management logs', 'edit management logs', 'view management logs',
            ],
            'Documentos de soporte' => [
                'upload support documents',
            ],
            'Casos castigados' => [
                'approve castigo cases', 'create castigo cases', 'delete castigo cases',
                'edit castigo cases', 'view castigo cases',
            ],
            'Reportes' => [
                'export reports', 'generate reports', 'view reports',
            ],
            'Dashboards' => [
                'view executive dashboard', 'view operative dashboard',
            ],
            'Administración' => [
                'manage advisors', 'manage branding', 'manage report templates',
                'manage roles', 'manage system config',
            ],
        ];

        $humanLabels = [
            'create portfolio'         => 'Crear documentos',
            'delete portfolio'         => 'Eliminar documentos',
            'edit portfolio'           => 'Editar documentos',
            'view portfolio'           => 'Ver cartera',
            'download support documents' => 'Descargar soportes',
            'create clients'           => 'Crear clientes',
            'delete clients'           => 'Eliminar clientes',
            'edit clients'             => 'Editar clientes',
            'view clients'             => 'Ver clientes',
            'upload collection loads'  => 'Cargar recaudos',
            'view collections'         => 'Ver recaudos',
            'upload portfolio loads'   => 'Cargar cartera',
            'create management logs'   => 'Registrar gestiones',
            'edit management logs'     => 'Editar gestiones',
            'view management logs'     => 'Ver gestiones',
            'upload support documents' => 'Subir documentos',
            'approve castigo cases'    => 'Aprobar casos',
            'create castigo cases'     => 'Crear casos',
            'delete castigo cases'     => 'Eliminar casos',
            'edit castigo cases'       => 'Editar casos',
            'view castigo cases'       => 'Ver casos',
            'export reports'           => 'Exportar reportes',
            'generate reports'         => 'Generar reportes',
            'view reports'             => 'Ver reportes',
            'view executive dashboard' => 'Dashboard ejecutivo',
            'view operative dashboard' => 'Dashboard operativo',
            'manage advisors'          => 'Gestionar asesores',
            'manage branding'          => 'Gestionar marca',
            'manage report templates'  => 'Plantillas de reportes',
            'manage roles'             => 'Gestionar roles',
            'manage system config'     => 'Config. del sistema',
        ];

        $allPermissions = Permission::orderBy('name')->get()->keyBy('name');
        $grouped        = [];
        $assigned       = [];

        foreach ($moduleMap as $module => $names) {
            foreach ($names as $name) {
                if ($perm = $allPermissions->get($name)) {
                    $grouped[$module][] = [
                        'id'    => $perm->id,
                        'name'  => $perm->name,
                        'label' => $humanLabels[$perm->name] ?? ucfirst($perm->name),
                    ];
                    $assigned[$perm->id] = true;
                }
            }
        }

        foreach ($allPermissions as $perm) {
            if (!isset($assigned[$perm->id])) {
                $grouped['Otros'][] = [
                    'id'    => $perm->id,
                    'name'  => $perm->name,
                    'label' => $humanLabels[$perm->name] ?? ucfirst($perm->name),
                ];
            }
        }

        return $grouped;
    }

    #[Computed]
    public function filteredPermissions(): array
    {
        $search = mb_strtolower(trim($this->permissionSearch));

        if ($search === '') {
            return $this->permissions;
        }

        $filtered = [];

        foreach ($this->permissions as $module => $perms) {
            $matches = array_values(array_filter($perms, function (array $perm) use ($search): bool {
                return str_contains(mb_strtolower($perm['label']), $search)
                    || str_contains(mb_strtolower($perm['name']), $search);
            }));

            if ($matches !== []) {
                $filtered[$module] = $matches;
            }
        }

        return $filtered;
    }

    public function createRole(): void
    {
        $this->validate([
            'newRoleName' => ['required', 'string', 'min:3', 'max:80', 'unique:roles,name'],
            'cloneRoleId' => ['nullable', 'exists:roles,id'],
        ]);

        $role = Role::create([
            'name' => trim($this->newRoleName),
        ]);

        if ($this->cloneRoleId) {
            $baseRole = Role::with('permissions')->findById($this->cloneRoleId);
            $role->syncPermissions($baseRole->permissions);
        }

        unset($this->roles, $this->filteredRoles);

        $this->newRoleName = '';
        $this->cloneRoleId = null;
        $this->confirmingDeleteRole = false;
        $this->confirmingInlineDeleteRoleId = null;

        $this->selectRole($role->id);

        Notification::make()
            ->title('Rol creado correctamente')
            ->success()
            ->send();
    }

    public function selectAllPermissions(): void
    {
        if (!$this->selectedRoleId) {
            return;
        }

        $this->rolePermissions = Permission::query()
            ->orderBy('id')
            ->pluck('id')
            ->map(fn (int $id): string => (string) $id)
            ->toArray();
    }

    public function clearAllPermissions(): void
    {
        if (!$this->selectedRoleId) {
            return;
        }

        $this->rolePermissions = [];
    }

    public function deleteSelectedRole(): void
    {
        if (!$this->selectedRoleId) {
            return;
        }

        if (!$this->confirmingDeleteRole) {
            $this->beginDeleteSelectedRole();
            return;
        }

        $role = $this->resolveRoleForDeletion($this->selectedRoleId);

        if (!$role) {
            $this->confirmingDeleteRole = false;
            return;
        }

        $this->executeRoleDeletion($role);
    }

    public function beginDeleteSelectedRole(): void
    {
        if (!$this->selectedRoleId) {
            return;
        }

        if (!$this->resolveRoleForDeletion($this->selectedRoleId)) {
            $this->confirmingDeleteRole = false;
            return;
        }

        $this->confirmingDeleteRole = true;
        $this->confirmingInlineDeleteRoleId = null;

        Notification::make()
            ->title('Confirma eliminación del rol')
            ->warning()
            ->send();
    }

    public function cancelDeleteSelectedRole(): void
    {
        $this->confirmingDeleteRole = false;
    }

    public function beginInlineDeleteRole(int $id): void
    {
        if (!$this->resolveRoleForDeletion($id)) {
            $this->confirmingInlineDeleteRoleId = null;
            return;
        }

        $this->confirmingInlineDeleteRoleId = $id;
        $this->confirmingDeleteRole = false;
    }

    public function cancelInlineDeleteRole(): void
    {
        $this->confirmingInlineDeleteRoleId = null;
    }

    public function deleteRoleById(int $id): void
    {
        if ($this->confirmingInlineDeleteRoleId !== $id) {
            $this->beginInlineDeleteRole($id);
            return;
        }

        $role = $this->resolveRoleForDeletion($id);

        if (!$role) {
            $this->confirmingInlineDeleteRoleId = null;
            return;
        }

        $this->executeRoleDeletion($role);
    }

    public function selectRole(int $id): void
    {
        $this->selectedRoleId  = $id;
        $this->confirmingDeleteRole = false;
        $this->confirmingInlineDeleteRoleId = null;
        $role                  = Role::findById($id);
        $this->rolePermissions = $role
            ? $role->permissions->pluck('id')->map(fn($v) => (string) $v)->toArray()
            : [];
    }

    public function toggleModulePermissions(string $module): void
    {
        $perms = $this->permissions[$module] ?? [];
        $ids   = collect($perms)->pluck('id')->map(fn($id) => (string) $id)->toArray();

        $current     = $this->rolePermissions;
        $allSelected = count(array_intersect($ids, $current)) === count($ids);

        $this->rolePermissions = $allSelected
            ? array_values(array_diff($current, $ids))
            : array_values(array_unique(array_merge($current, $ids)));
    }

    public function saveRolePermissions(): void
    {
        if (!$this->selectedRoleId) {
            return;
        }

        $role = Role::findById($this->selectedRoleId);

        if (!$role) {
            Notification::make()->title('Rol no encontrado')->danger()->send();
            return;
        }

        if (mb_strtolower($role->name) === 'admin') {
            $permissions = Permission::query()->orderBy('id')->get();

            $role->syncPermissions($permissions);

            $this->rolePermissions = $permissions
                ->pluck('id')
                ->map(fn (int $id): string => (string) $id)
                ->toArray();

            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            unset($this->roles);

            Notification::make()
                ->title('El rol admin conserva acceso total')
                ->warning()
                ->send();

            return;
        }

        $permissionIds = collect($this->rolePermissions)
            ->map(fn ($id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values();

        $permissions = Permission::query()
            ->whereIn('id', $permissionIds->all())
            ->get();

        $role->syncPermissions($permissions);

        $this->rolePermissions = $permissions
            ->pluck('id')
            ->map(fn (int $id): string => (string) $id)
            ->toArray();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Refresh counts
        unset($this->roles);

        Notification::make()
            ->title('Permisos de "' . $role->name . '" actualizados')
            ->success()
            ->send();
    }

    private function resolveRoleForDeletion(int $roleId): ?Role
    {
        /** @var Role|null $role */
        $role = Role::query()->withCount('users')->find($roleId);

        if (!$role) {
            Notification::make()->title('Rol no encontrado')->danger()->send();
            return null;
        }

        if (mb_strtolower($role->name) === 'admin') {
            Notification::make()
                ->title('El rol admin está protegido')
                ->danger()
                ->send();
            return null;
        }

        if (($role->users_count ?? 0) > 0) {
            Notification::make()
                ->title('Reasigna los usuarios antes de eliminar el rol')
                ->warning()
                ->send();
            return null;
        }

        return $role;
    }

    private function executeRoleDeletion(Role $role): void
    {
        $roleId = $role->id;
        $roleName = $role->name;

        $role->delete();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        if ($this->selectedRoleId === $roleId) {
            $this->selectedRoleId = null;
            $this->rolePermissions = [];
        }

        $this->confirmingDeleteRole = false;
        $this->confirmingInlineDeleteRoleId = null;

        unset($this->roles, $this->filteredRoles);

        Notification::make()
            ->title('Rol "' . $roleName . '" eliminado')
            ->success()
            ->send();
    }
}
