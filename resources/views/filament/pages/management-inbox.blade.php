<x-filament-panels::page>

@include('filament.pages.partials.modern-dashboard-styles')

@push('styles')
<style>
.data-table tr.priority-critical td { border-left: 3px solid var(--mcm-red); }
.data-table tr.priority-high td { border-left: 3px solid var(--mcm-amber); }
.data-table tr.priority-medium td { border-left: 3px solid var(--mcm-amber); }
.data-table tr.priority-low td { border-left: 3px solid var(--mcm-green); }

.inbox-card {
    background: var(--mcm-surface);
    border: 1px solid var(--mcm-border);
    border-radius: 12px;
    overflow: hidden;
}
</style>
@endpush

<div class="mcm-modern-page space-y-5">

    <div class="page-hero mcm-reveal">
        <div>
            <h1>Bandeja de Gestión</h1>
            <p>{{ count($this->items) }} documentos · Ordenados por prioridad de mora</p>
        </div>
    </div>

    <div class="filter-bar mcm-reveal">
        <div style="display:flex;align-items:flex-end;gap:0.75rem;flex-wrap:wrap;">
            <div>
                <p class="filter-label">Buscar</p>
                <input type="text" wire:model="search" placeholder="Cliente, NIT o documento…" class="filter-input" style="min-width:200px"/>
            </div>
            <div>
                <p class="filter-label">Asesor</p>
                <select wire:model="advisorId" class="filter-input">
                    <option value="">Todos</option>
                    @foreach($this->advisorOptions as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach
                </select>
            </div>
            <div>
                <p class="filter-label">Mora mínima</p>
                <select wire:model="minBucket" class="filter-input">
                    <option value="">Todos</option>
                    <option value=">0">Vencidos (+0d)</option>
                    <option value=">30">+30 días</option>
                    <option value=">60">+60 días</option>
                    <option value=">90">Crítico (+90d)</option>
                </select>
            </div>
            <button wire:click="applyFilter" class="btn-primary"><x-heroicon-o-funnel style="width:1rem;height:1rem;"/>Filtrar</button>
        </div>
    </div>

    <div class="inbox-card mcm-reveal">
        <div style="overflow-x:auto;max-height:70vh;overflow-y:auto;">
            <table class="data-table">
                <thead>
                    <tr><th></th><th>Cliente</th><th>Documento</th><th class="text-right">Días Mora</th><th class="text-right">Saldo</th><th>Riesgo</th><th>Asesor</th><th>Última Gestión</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($this->items as $row)
                    @php
                        $row = (object)$row;
                        $dias = $row->days_overdue ?? 0;
                        $pClass = $dias > 90 ? 'priority-critical' : ($dias > 60 ? 'priority-high' : ($dias > 30 ? 'priority-medium' : 'priority-low'));
                        $riskColors = ['critical'=>'badge-red','high'=>'badge-red','medium'=>'badge-amber','low'=>'badge-amber','normal'=>'badge-gray'];
                        $riskLabels = ['critical'=>'Crítico','high'=>'Alto','medium'=>'Medio','low'=>'Bajo','normal'=>'Normal'];
                        $typeLabels = ['call'=>'Llamada','email'=>'Correo','visit'=>'Visita','agreement'=>'Acuerdo','legal'=>'Jurídico'];
                    @endphp
                    <tr class="{{ $pClass }}">
                        <td style="width:1px;padding:0;"></td>
                        <td>
                            <p style="font-weight:600;color:var(--mcm-text-strong);">{{ $row->client_name }}</p>
                            <p style="font-size:0.72rem;color:var(--mcm-muted);">{{ $row->nit }}</p>
                        </td>
                        <td style="font-family:var(--font-mono,monospace);font-size:0.78rem;color:var(--mcm-muted);">{{ $row->document_number }}</td>
                        <td class="text-right">
                            @if($dias > 90)<span class="badge-pill badge-red">{{ $dias }}d</span>
                            @elseif($dias > 60)<span class="badge-pill badge-red" style="background:var(--mcm-amber-bg);color:var(--mcm-red);">{{ $dias }}d</span>
                            @elseif($dias > 30)<span class="badge-pill badge-amber">{{ $dias }}d</span>
                            @elseif($dias > 0)<span class="badge-pill badge-green">{{ $dias }}d</span>
                            @else<span class="badge-pill badge-gray">Corriente</span>
                            @endif
                        </td>
                        <td class="text-right money-value">${{ number_format($row->pending_amount, 0, ',', '.') }}</td>
                        <td><span class="badge-pill {{ $riskColors[$row->risk_level] ?? 'badge-gray' }}">{{ $riskLabels[$row->risk_level] ?? '—' }}</span></td>
                        <td style="font-size:0.78rem;color:var(--mcm-muted);">{{ $row->advisor_name ?? '—' }}</td>
                        <td style="font-size:0.78rem;color:var(--mcm-muted);">
                            @if($row->last_contact)
                                {{ \Carbon\Carbon::parse($row->last_contact)->format('d/m/Y') }}
                                @if($row->last_type) · {{ $typeLabels[$row->last_type] ?? $row->last_type }} @endif
                            @else
                                <span class="badge-pill badge-red">Sin gestión</span>
                            @endif
                        </td>
                        <td></td>
                    </tr>
                    @empty
                    <tr><td colspan="9" style="text-align:center;padding:3rem 1rem;">
                        <div class="empty-state">
                            <x-heroicon-o-check-circle style="width:2.5rem;height:2.5rem;color:var(--mcm-soft);"/>
                            <p style="font-size:0.88rem;font-weight:500;">Sin documentos pendientes de gestión</p>
                        </div>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

</x-filament-panels::page>
