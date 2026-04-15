<x-filament-panels::page>
@include('filament.pages.partials.modern-dashboard-styles')

@push('styles')
<style>
/* ── Role Management ─────────────────────────────────────── */
.role-mgmt .rm-layout {
    align-items: start;
    display: grid;
    gap: 1.1rem;
    grid-template-columns: 17rem minmax(0,1fr);
}

/* ── Left panel ── */
.role-mgmt .rm-roles-panel {
    background: var(--mcm-surface);
    border: 1px solid var(--mcm-border);
    border-radius: 12px;
    box-shadow: var(--mcm-shadow-soft);
    overflow: hidden;
    position: sticky;
    top: 1rem;
}

.role-mgmt .rm-panel-head {
    align-items: center;
    background: var(--mcm-surface-soft);
    border-bottom: 1px solid var(--mcm-border);
    display: flex;
    gap: .6rem;
    justify-content: space-between;
    padding: .8rem 1rem;
}

.role-mgmt .rm-panel-head-left {
    align-items: center;
    display: flex;
    gap: .5rem;
}

.role-mgmt .rm-panel-icon {
    align-items: center;
    background: var(--mcm-accent-soft);
    border: 1px solid color-mix(in srgb,var(--mcm-accent) 22%,var(--mcm-border));
    border-radius: 7px;
    color: var(--mcm-accent-strong);
    display: inline-flex;
    height: 1.85rem;
    justify-content: center;
    width: 1.85rem;
}
.role-mgmt .rm-panel-icon svg { height:.82rem;width:.82rem; }

.role-mgmt .rm-panel-title {
    color: var(--mcm-text);
    font-size: .84rem;
    font-weight: 760;
}

.role-mgmt .rm-search-wrap {
    border-bottom: 1px solid var(--mcm-border);
    padding: .72rem;
}

.role-mgmt .rm-search-input,
.role-mgmt .rm-select-input {
    background: var(--mcm-surface-soft);
    border: 1px solid var(--mcm-border);
    border-radius: 8px;
    color: var(--mcm-text);
    font-size: .78rem;
    min-height: 2.35rem;
    padding: .45rem .72rem;
    width: 100%;
}

.role-mgmt .rm-search-input:focus,
.role-mgmt .rm-select-input:focus {
    border-color: var(--mcm-accent);
    box-shadow: 0 0 0 2.5px color-mix(in srgb,var(--mcm-accent) 16%,transparent);
    outline: none;
}

.role-mgmt .rm-roles-list {
    display: flex;
    flex-direction: column;
    gap: .22rem;
    max-height: calc(100vh - 17rem);
    overflow-y: auto;
    padding: .55rem;
    scrollbar-width: thin;
}

/* Role card */
.role-mgmt .rm-role-item {
    border: 1.5px solid transparent;
    border-radius: 8px;
    cursor: pointer;
    overflow: hidden;
    padding: .68rem .82rem .68rem 1rem;
    position: relative;
}

.role-mgmt .rm-role-item::before {
    background: var(--mcm-accent);
    border-radius: 0 2px 2px 0;
    bottom: 20%;
    content: '';
    left: 0;
    opacity: 0;
    position: absolute;
    top: 20%;
    width: 3px;
}

.role-mgmt .rm-role-item:hover {
    background: var(--mcm-surface-soft);
    border-color: var(--mcm-border);
}

.role-mgmt .rm-role-item.rm-active {
    background: var(--mcm-accent-soft);
    border-color: color-mix(in srgb,var(--mcm-accent) 38%,var(--mcm-border));
    box-shadow: 0 0 0 1px color-mix(in srgb,var(--mcm-accent) 10%,transparent) inset;
}

.role-mgmt .rm-role-item.rm-active::before { opacity: 1; }

.role-mgmt .rm-role-name {
    color: var(--mcm-text);
    font-size: .84rem;
    font-weight: 720;
    margin-bottom: .38rem;
}

.role-mgmt .rm-role-top {
    align-items: flex-start;
    display: flex;
    gap: .5rem;
    justify-content: space-between;
}

.role-mgmt .rm-role-actions-inline {
    align-items: center;
    display: flex;
    flex-shrink: 0;
    gap: .28rem;
}

.role-mgmt .rm-role-mini-btn {
    align-items: center;
    background: var(--mcm-surface);
    border: 1px solid var(--mcm-border);
    border-radius: 6px;
    color: var(--mcm-muted);
    cursor: pointer;
    display: inline-flex;
    font-size: .66rem;
    font-weight: 700;
    gap: .2rem;
    min-height: 1.55rem;
    padding: .2rem .4rem;
}

.role-mgmt .rm-role-mini-btn:hover {
    border-color: color-mix(in srgb,var(--mcm-accent) 36%,var(--mcm-border));
    color: var(--mcm-accent-strong);
}

.role-mgmt .rm-role-mini-btn.danger {
    border-color: color-mix(in srgb,var(--mcm-red) 32%,var(--mcm-border));
    color: var(--mcm-red);
}

.role-mgmt .rm-role-mini-btn.danger:hover {
    border-color: color-mix(in srgb,var(--mcm-red) 55%,var(--mcm-border));
    color: color-mix(in srgb,var(--mcm-red) 82%,black);
}

.role-mgmt .rm-role-mini-btn:disabled {
    cursor: not-allowed;
    opacity: .45;
}

.role-mgmt .rm-role-item.rm-active .rm-role-name { color: var(--mcm-accent-strong); }

.role-mgmt .rm-role-meta { display:flex;flex-wrap:wrap;gap:.28rem; }

.role-mgmt .rm-role-actions {
    border-top: 1px solid var(--mcm-border);
    padding: .75rem;
}

.role-mgmt .rm-role-actions-title {
    color: var(--mcm-muted);
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .04em;
    margin-bottom: .5rem;
    text-transform: uppercase;
}

.role-mgmt .rm-create-grid {
    display: grid;
    gap: .48rem;
}

/* ── Right panel ── */
.role-mgmt .rm-perms-panel {
    background: var(--mcm-surface);
    border: 1px solid var(--mcm-border);
    border-radius: 12px;
    box-shadow: var(--mcm-shadow-soft);
    display: flex;
    flex-direction: column;
    min-height: 28rem;
    overflow: hidden;
}

.role-mgmt .rm-perms-head {
    align-items: center;
    background: var(--mcm-surface-soft);
    border-bottom: 1px solid var(--mcm-border);
    display: flex;
    gap: 1rem;
    justify-content: space-between;
    padding: .82rem 1.1rem;
}

.role-mgmt .rm-perms-title { color:var(--mcm-text);font-size:.9rem;font-weight:760; }
.role-mgmt .rm-perms-sub   { color:var(--mcm-muted);font-size:.73rem;margin-top:.08rem; }
.role-mgmt .rm-perms-sub.rm-hint-muted { color: var(--mcm-muted); margin-top: .22rem; }
.role-mgmt .rm-perms-sub.rm-hint-danger { color: var(--mcm-red); margin-top: .22rem; }

.role-mgmt .rm-head-tools {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
    justify-content: flex-end;
}

.role-mgmt .rm-perm-search {
    min-width: 15rem;
}

.role-mgmt .rm-filter-indicator {
    color: var(--mcm-muted);
    font-size: .72rem;
}

/* Empty state */
.role-mgmt .rm-empty {
    align-items: center;
    display: flex;
    flex: 1;
    flex-direction: column;
    gap: .95rem;
    justify-content: center;
    padding: 5rem 2rem;
    text-align: center;
}

.role-mgmt .rm-empty-ring {
    align-items: center;
    background: var(--mcm-surface-soft);
    border: 2px dashed var(--mcm-border);
    border-radius: 50%;
    color: var(--mcm-muted);
    display: flex;
    height: 4.5rem;
    justify-content: center;
    opacity: .55;
    width: 4.5rem;
}
.role-mgmt .rm-empty-ring svg { height:2rem;width:2rem; }
.role-mgmt .rm-empty-title { color:var(--mcm-text);font-size:.96rem;font-weight:700; }
.role-mgmt .rm-empty-copy  { color:var(--mcm-muted);font-size:.81rem;line-height:1.55;max-width:19rem; }

/* Modules wrapper */
.role-mgmt .rm-modules-wrap {
    display: block;
    flex: 1;
    max-height: calc(100vh - 22rem);
    overflow-y: auto;
    padding: .9rem 1.1rem;
    scrollbar-width: thin;
}

/* Module block */
.role-mgmt .rm-module {
    border: 1px solid var(--mcm-border);
    border-radius: 9px;
    flex-shrink: 0;
    overflow: hidden;
}

.role-mgmt .rm-module + .rm-module {
    margin-top: .5rem;
}

.role-mgmt .rm-module-head {
    align-items: center;
    background: var(--mcm-surface-soft);
    display: flex;
    gap: .65rem;
    justify-content: space-between;
    padding: .58rem .82rem;
}
.role-mgmt .rm-module-head:hover {
    background: color-mix(in srgb,var(--mcm-surface-soft) 75%,var(--mcm-surface-strong));
}

.role-mgmt .rm-module-left { align-items:center;display:flex;flex:1;gap:.58rem;min-width:0; }

.role-mgmt .rm-module-micon {
    align-items: center;
    background: var(--mcm-accent-soft);
    border: 1px solid color-mix(in srgb,var(--mcm-accent) 18%,var(--mcm-border));
    border-radius: 6px;
    color: var(--mcm-accent-strong);
    display: inline-flex;
    flex-shrink: 0;
    height: 1.55rem;
    justify-content: center;
    width: 1.55rem;
}
.role-mgmt .rm-module-micon svg { height:.72rem;width:.72rem; }

.role-mgmt .rm-module-name { color:var(--mcm-text);font-size:.8rem;font-weight:720; }

.role-mgmt .rm-module-right {
    align-items: center;
    display: flex;
    flex-shrink: 0;
    gap: .6rem;
}

.role-mgmt .rm-mod-counter {
    color: var(--mcm-muted);
    font-size: .72rem;
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
}
.role-mgmt .rm-mod-counter strong { color:var(--mcm-accent-strong);font-weight:760; }
.role-mgmt .rm-mod-counter.zero   { visibility:hidden; }

.role-mgmt .rm-select-all-cb {
    accent-color: var(--mcm-accent);
    cursor: pointer;
    height: .82rem;
    width: .82rem;
}

/* Permission grid */
.role-mgmt .rm-perm-grid {
    display: flex;
    flex-direction: column;
    gap: .35rem;
    padding: .52rem;
}

.role-mgmt .rm-perm-item {
    align-items: center;
    border-radius: 6px;
    display: grid;
    gap: .55rem;
    grid-template-columns: auto minmax(0,1fr) auto;
    padding: .52rem .58rem;
}
.role-mgmt .rm-perm-item:hover { background: var(--mcm-surface-soft); }

.role-mgmt .rm-perm-item.is-enabled {
    background: color-mix(in srgb,var(--mcm-accent-soft) 56%,var(--mcm-surface));
}

.role-mgmt .rm-perm-cb {
    accent-color: var(--mcm-accent);
    cursor: pointer;
    flex-shrink: 0;
    height: .82rem;
    width: .82rem;
}

.role-mgmt .rm-perm-lbl {
    color: var(--mcm-text);
    cursor: pointer;
    display: flex;
    flex-direction: column;
    gap: .08rem;
    min-width: 0;
}

.role-mgmt .rm-perm-title {
    color: var(--mcm-text);
    font-size: .77rem;
    font-weight: 700;
    line-height: 1.25;
}

.role-mgmt .rm-perm-key {
    color: var(--mcm-muted);
    font-family: Consolas, 'Courier New', monospace;
    font-size: .68rem;
    line-height: 1.25;
}

.role-mgmt .rm-perm-state {
    border: 1px solid var(--mcm-border);
    border-radius: 999px;
    font-size: .66rem;
    font-weight: 700;
    margin-left: auto;
    padding: .15rem .45rem;
    white-space: nowrap;
}

.role-mgmt .rm-perm-state.on {
    background: var(--mcm-accent-soft);
    border-color: color-mix(in srgb,var(--mcm-accent) 30%,var(--mcm-border));
    color: var(--mcm-accent-strong);
}

.role-mgmt .rm-perm-state.off {
    background: var(--mcm-surface-soft);
    color: var(--mcm-muted);
}

.role-mgmt .rm-btn-state {
    align-items: center;
    display: inline-flex;
    gap: .45rem;
}

.role-mgmt .rm-btn-danger {
    border-color: color-mix(in srgb,var(--mcm-red) 32%,var(--mcm-border));
    color: var(--mcm-red);
}

.role-mgmt .rm-btn-danger:hover {
    border-color: color-mix(in srgb,var(--mcm-red) 48%,var(--mcm-border));
    color: color-mix(in srgb,var(--mcm-red) 82%,black);
}

.role-mgmt .btn-primary.rm-btn-danger-solid {
    background: var(--mcm-red);
    border-color: var(--mcm-red);
    box-shadow: none;
    color: #fff;
}

.role-mgmt .btn-primary.rm-btn-danger-solid:hover {
    background: color-mix(in srgb,var(--mcm-red) 85%,black);
    border-color: color-mix(in srgb,var(--mcm-red) 85%,black);
}

/* Save bar */
.role-mgmt .rm-save-bar {
    align-items: center;
    background: var(--mcm-surface);
    border-top: 1px solid var(--mcm-border);
    display: flex;
    gap: 1rem;
    justify-content: space-between;
    padding: .75rem 1.1rem;
}

.role-mgmt .rm-save-hint {
    color: var(--mcm-muted);
    font-size: .73rem;
    line-height: 1.45;
}

/* Loading overlay on modules */
.role-mgmt .rm-loading-veil {
    align-items: center;
    background: color-mix(in srgb,var(--mcm-surface) 85%,transparent);
    backdrop-filter: blur(2px);
    border-radius: 12px;
    bottom: 0;
    display: flex;
    justify-content: center;
    left: 0;
    position: absolute;
    right: 0;
    top: 0;
    z-index: 10;
}

.role-mgmt .rm-spinner {
    border: 2.5px solid var(--mcm-border);
    border-radius: 50%;
    border-top-color: var(--mcm-accent);
    height: 1.75rem;
    width: 1.75rem;
}

@media (max-width: 960px) {
    .role-mgmt .rm-layout { grid-template-columns: 1fr; }
    .role-mgmt .rm-roles-panel { position: static; }
    .role-mgmt .rm-roles-list, .role-mgmt .rm-modules-wrap { max-height: none; }
    .role-mgmt .rm-head-tools { width: 100%; justify-content: stretch; }
    .role-mgmt .rm-perm-search { min-width: 100%; }
}
</style>
@endpush

@php
$moduleIcons = [
    'Cartera'               => 'heroicon-o-document-text',
    'Clientes'              => 'heroicon-o-users',
    'Recaudos'              => 'heroicon-o-banknotes',
    'Cargas'                => 'heroicon-o-arrow-up-tray',
    'Gestión de cartera'    => 'heroicon-o-clipboard-document-check',
    'Documentos de soporte' => 'heroicon-o-paper-clip',
    'Casos castigados'      => 'heroicon-o-exclamation-triangle',
    'Reportes'              => 'heroicon-o-chart-bar',
    'Dashboards'            => 'heroicon-o-squares-2x2',
    'Administración'        => 'heroicon-o-cog-6-tooth',
    'Otros'                 => 'heroicon-o-puzzle-piece',
];

$displayedModules    = $this->filteredPermissions;
$totalPermsSelected  = count($this->rolePermissions);
$totalPermsAvailable = collect($this->permissions)->flatten(1)->count();
$visiblePermsCount   = collect($displayedModules)->flatten(1)->count();
$selectedRole        = $this->selectedRoleId ? $this->roles->firstWhere('id', $this->selectedRoleId) : null;
$selectedRoleUsers   = (int) ($selectedRole->users_count ?? 0);
$isProtectedRole     = $selectedRole ? mb_strtolower($selectedRole->name) === 'admin' : false;
$canDeleteRole       = $selectedRole && !$isProtectedRole && $selectedRoleUsers === 0;

$deleteRoleHint = null;
if ($selectedRole) {
    if ($isProtectedRole) {
        $deleteRoleHint = 'El rol admin está protegido y no se puede eliminar.';
    } elseif ($selectedRoleUsers > 0) {
        $deleteRoleHint = 'Este rol tiene ' . $selectedRoleUsers . ' usuario(s). Reasígnalos antes de eliminar.';
    } else {
        $deleteRoleHint = 'Puedes eliminar este rol si ya no se usa.';
    }
}
@endphp

<div class="mcm-modern-page role-mgmt space-y-5">

    {{-- Hero --}}
    <section class="page-hero">
        <div>
            <p class="dash-section-title" style="margin-bottom:.3rem;">Configuración</p>
            <h1>Gestión Avanzada de Roles</h1>
            <p>Asigna permisos granulares a cada rol del sistema.</p>
        </div>
        @if($this->selectedRoleId)
        <div style="display:flex;align-items:center;gap:.6rem;flex-shrink:0;">
            <span class="badge-pill badge-blue">{{ $totalPermsSelected }} / {{ $totalPermsAvailable }} permisos activos</span>
        </div>
        @endif
    </section>

    {{-- Layout --}}
    <div class="rm-layout">

        {{-- LEFT: roles --}}
        <div class="rm-roles-panel">
            <div class="rm-panel-head">
                <div class="rm-panel-head-left">
                    <span class="rm-panel-icon"><x-heroicon-o-shield-check /></span>
                    <span class="rm-panel-title">Roles del sistema</span>
                </div>
                <span class="badge-pill badge-gray">{{ $this->roles->count() }}</span>
            </div>

            <div class="rm-search-wrap">
                <input type="text"
                       wire:model.live.debounce.300ms="roleSearch"
                       class="rm-search-input"
                       placeholder="Buscar rol por nombre..."/>
            </div>

            <div class="rm-roles-list">
                @forelse($this->filteredRoles as $role)
                @php
                    $isRoleProtected = mb_strtolower($role->name) === 'admin';
                    $roleUsersCount = (int) ($role->users_count ?? 0);
                    $canDeleteFromCard = !$isRoleProtected && $roleUsersCount === 0;
                    $isConfirmingInlineDelete = $this->confirmingInlineDeleteRoleId === $role->id;
                    $deleteRoleTitle = $isRoleProtected
                        ? 'Rol protegido'
                        : ($roleUsersCount > 0 ? 'Tiene usuarios asignados' : 'Eliminar rol');
                @endphp
                <div wire:click="selectRole({{ $role->id }})"
                     class="rm-role-item {{ $this->selectedRoleId == $role->id ? 'rm-active' : '' }}">
                    <div class="rm-role-top">
                        <div class="rm-role-name">{{ $role->name }}</div>

                        <div class="rm-role-actions-inline" wire:click.stop>
                            @if($isConfirmingInlineDelete)
                            <button type="button"
                                    class="rm-role-mini-btn danger"
                                    wire:click.stop="deleteRoleById({{ $role->id }})">
                                Confirmar
                            </button>
                            <button type="button"
                                    class="rm-role-mini-btn"
                                    wire:click.stop="cancelInlineDeleteRole">
                                Cancelar
                            </button>
                            @else
                            <button type="button"
                                    class="rm-role-mini-btn danger"
                                    wire:click.stop="beginInlineDeleteRole({{ $role->id }})"
                                    @if(!$canDeleteFromCard) disabled @endif
                                    title="{{ $deleteRoleTitle }}">
                                <x-heroicon-o-trash style="width:.72rem;height:.72rem"/>
                            </button>
                            @endif
                        </div>
                    </div>
                    <div class="rm-role-meta">
                        <span class="badge-pill {{ $this->selectedRoleId == $role->id ? 'badge-blue' : 'badge-gray' }}"
                              style="font-size:.7rem;">
                            {{ $role->users_count ?? 0 }} {{ ($role->users_count ?? 0) == 1 ? 'usuario' : 'usuarios' }}
                        </span>
                        <span class="badge-pill badge-gray" style="font-size:.7rem;">
                            {{ $role->permissions_count ?? 0 }} permisos
                        </span>
                    </div>
                </div>
                @empty
                <p style="color:var(--mcm-muted);font-size:.82rem;padding:.5rem .25rem;text-align:center;">
                    Sin resultados para la búsqueda actual.
                </p>
                @endforelse
            </div>

            <div class="rm-role-actions">
                <div class="rm-role-actions-title">Crear nuevo rol</div>
                <form wire:submit.prevent="createRole" class="rm-create-grid">
                    <input type="text"
                           wire:model="newRoleName"
                           class="rm-search-input"
                           placeholder="Ej: supervisor regional"/>
                    <select wire:model="cloneRoleId" class="rm-select-input">
                        <option value="">Sin plantilla base</option>
                        @foreach($this->roles as $baseRole)
                            <option value="{{ $baseRole->id }}">Clonar permisos de {{ $baseRole->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-ghost" style="width:100%;">
                        <x-heroicon-o-plus style="width:.88rem;height:.88rem"/>
                        Crear rol
                    </button>
                </form>
            </div>
        </div>

        {{-- RIGHT: permissions --}}
        <div class="rm-perms-panel" style="position:relative;">

            {{-- Veil loading on selectRole --}}
            <div class="rm-loading-veil" wire:loading wire:target="selectRole">
                <div class="rm-spinner"></div>
            </div>

            {{-- Head --}}
            <div class="rm-perms-head">
                <div>
                    <div class="rm-perms-title">
                        @if($this->selectedRoleId)
                            {{ $this->roles->firstWhere('id', $this->selectedRoleId)?->name }}
                        @else
                            Matriz de permisos
                        @endif
                    </div>
                    @if($this->selectedRoleId)
                    <div class="rm-perms-sub">
                        {{ $totalPermsSelected }} de {{ $totalPermsAvailable }} permisos habilitados
                    </div>
                    @if($deleteRoleHint)
                    <div class="rm-perms-sub {{ $canDeleteRole ? 'rm-hint-muted' : 'rm-hint-danger' }}">
                        {{ $deleteRoleHint }}
                    </div>
                    @endif
                    @endif
                </div>
                <div class="rm-head-tools">
                    <input type="text"
                           wire:model.live.debounce.300ms="permissionSearch"
                           class="rm-search-input rm-perm-search"
                           placeholder="Buscar permiso por nombre o etiqueta..."/>

                    @if($this->permissionSearch !== '')
                        <span class="rm-filter-indicator">Mostrando {{ $visiblePermsCount }} de {{ $totalPermsAvailable }}</span>
                    @endif

                    @if($this->selectedRoleId)
                    <button wire:click="selectAllPermissions" class="btn-ghost">
                        <x-heroicon-o-check-badge style="width:.88rem;height:.88rem"/>
                        Seleccionar todo
                    </button>

                    <button wire:click="clearAllPermissions" class="btn-ghost">
                        <x-heroicon-o-x-mark style="width:.88rem;height:.88rem"/>
                        Limpiar
                    </button>

                    @if($this->confirmingDeleteRole)
                    <button wire:click="cancelDeleteSelectedRole" class="btn-ghost">
                        <x-heroicon-o-arrow-uturn-left style="width:.88rem;height:.88rem"/>
                        Cancelar
                    </button>

                    <button wire:click="deleteSelectedRole"
                            wire:loading.attr="disabled"
                            wire:target="deleteSelectedRole"
                            class="btn-primary rm-btn-danger-solid">
                        <span wire:loading.remove wire:target="deleteSelectedRole" class="rm-btn-state">
                            <x-heroicon-o-trash style="width:.88rem;height:.88rem"/>
                            Confirmar eliminación
                        </span>
                        <span wire:loading wire:target="deleteSelectedRole" class="rm-btn-state">
                            <x-heroicon-o-arrow-path style="width:.88rem;height:.88rem;"/>
                            Eliminando…
                        </span>
                    </button>
                    @else
                    <button wire:click="beginDeleteSelectedRole"
                            class="btn-ghost rm-btn-danger"
                            @if(!$canDeleteRole) disabled @endif>
                        <x-heroicon-o-trash style="width:.88rem;height:.88rem"/>
                        Eliminar rol
                    </button>
                    @endif

                    <button wire:click="saveRolePermissions"
                            wire:loading.attr="disabled"
                            wire:target="saveRolePermissions"
                            class="btn-primary">
                        <span wire:loading.remove wire:target="saveRolePermissions" class="rm-btn-state">
                            <x-heroicon-o-check style="width:.88rem;height:.88rem"/>
                            Guardar cambios
                        </span>
                        <span wire:loading wire:target="saveRolePermissions" class="rm-btn-state">
                            <x-heroicon-o-arrow-path style="width:.88rem;height:.88rem;"/>
                            Guardando…
                        </span>
                    </button>
                    @endif
                </div>
            </div>

            @if(!$this->selectedRoleId)
            {{-- Empty state --}}
            <div class="rm-empty">
                <div class="rm-empty-ring"><x-heroicon-o-shield-exclamation /></div>
                <div class="rm-empty-title">Selecciona un rol</div>
                <p class="rm-empty-copy">
                    Elige un rol del panel izquierdo para ver y configurar sus permisos de acceso al sistema.
                </p>
            </div>

            @else
            {{-- Modules --}}
            <div class="rm-modules-wrap">
                @forelse($displayedModules as $module => $perms)
                @php
                    $permIds      = collect($perms)->pluck('id')->map(fn($id) => (string)$id)->toArray();
                    $selCount     = count(array_intersect($permIds, $this->rolePermissions));
                    $totalCount   = count($perms);
                    $allSelected  = $selCount === $totalCount && $totalCount > 0;
                    $modIcon      = $moduleIcons[$module] ?? 'heroicon-o-puzzle-piece';
                @endphp

                <div class="rm-module">
                    <div class="rm-module-head">

                        <div class="rm-module-left">
                            <span class="rm-module-micon">
                                <x-dynamic-component :component="$modIcon" />
                            </span>
                            <span class="rm-module-name">{{ $module }}</span>
                            <span class="badge-pill badge-gray" style="font-size:.67rem;">{{ $totalCount }}</span>
                        </div>

                        <div class="rm-module-right">
                            <span class="rm-mod-counter {{ $selCount === 0 ? 'zero' : '' }}">
                                <strong>{{ $selCount }}</strong> / {{ $totalCount }}
                            </span>

                            {{-- Select all toggle --}}
                            <div>
                                <input type="checkbox"
                                       class="rm-select-all-cb"
                                       {{ $allSelected ? 'checked' : '' }}
                                       wire:click.prevent="toggleModulePermissions('{{ $module }}')"
                                       title="Seleccionar todo el módulo"/>
                            </div>
                        </div>
                    </div>

                    <div class="rm-perm-grid">
                        @foreach($perms as $perm)
                        @php $hasAccess = in_array((string) $perm['id'], $this->rolePermissions, true); @endphp
                        <div class="rm-perm-item {{ $hasAccess ? 'is-enabled' : '' }}">
                            <input type="checkbox"
                                   id="perm_{{ $perm['id'] }}"
                                   class="rm-perm-cb"
                                   wire:model="rolePermissions"
                                   value="{{ $perm['id'] }}"/>
                            <label for="perm_{{ $perm['id'] }}" class="rm-perm-lbl">
                                <span class="rm-perm-title">{{ $perm['label'] }}</span>
                                <span class="rm-perm-key">{{ $perm['name'] }}</span>
                            </label>
                            <span class="rm-perm-state {{ $hasAccess ? 'on' : 'off' }}">
                                {{ $hasAccess ? 'Con acceso' : 'Sin acceso' }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @empty
                <div style="color:var(--mcm-muted);font-size:.82rem;padding:1rem .35rem;text-align:center;">
                    No hay permisos que coincidan con ese filtro.
                </div>
                @endforelse
            </div>

            {{-- Save bar (bottom) --}}
            <div class="rm-save-bar">
                <p class="rm-save-hint">
                    Los cambios se aplican de inmediato al guardar. Las sesiones activas se actualizarán en el próximo request.
                </p>
                <button wire:click="saveRolePermissions"
                        wire:loading.attr="disabled"
                        wire:target="saveRolePermissions"
                        class="btn-primary">
                    <span wire:loading.remove wire:target="saveRolePermissions" class="rm-btn-state">
                        <x-heroicon-o-check style="width:.88rem;height:.88rem"/>
                        Guardar
                    </span>
                    <span wire:loading wire:target="saveRolePermissions" class="rm-btn-state">
                        <x-heroicon-o-arrow-path style="width:.88rem;height:.88rem;"/>
                        Guardando…
                    </span>
                </button>
            </div>
            @endif
        </div>

    </div>
</div>
</x-filament-panels::page>
