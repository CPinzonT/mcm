<x-filament-panels::page>

@include('filament.pages.partials.modern-dashboard-styles')

@push('styles')
<style>
.commitments-dashboard .commit-hero-meta {
    display: grid;
    gap: 0.75rem;
    grid-template-columns: repeat(2, minmax(8rem, 1fr));
    min-width: min(100%, 24rem);
}

.commitments-dashboard .commit-hero-stat {
    background: var(--mcm-surface-soft);
    border: 1px solid var(--mcm-border);
    border-radius: 8px;
    min-height: 4.25rem;
    padding: 0.85rem 0.95rem;
}

.commitments-dashboard .commit-label,
.commitments-dashboard .commit-hero-stat span {
    color: var(--mcm-muted);
    display: block;
    font-size: 0.72rem;
    font-weight: 500;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.commitments-dashboard .commit-hero-stat strong {
    color: var(--mcm-text-strong);
    display: block;
    font-size: 1.25rem;
    font-weight: 600;
    letter-spacing: -0.02em;
    line-height: 1.1;
    margin-top: 0.3rem;
}

.commitments-dashboard .commit-filter-grid {
    align-items: end;
    display: grid;
    gap: 0.9rem;
    grid-template-columns: minmax(14rem, 0.4fr) minmax(12rem, 1fr) auto;
}

.commitments-dashboard .commit-filter-copy,
.commitments-dashboard .commit-section-copy {
    color: var(--mcm-muted);
    font-size: 0.82rem;
    line-height: 1.45;
}

.commitments-dashboard .commit-section-head {
    align-items: end;
    display: flex;
    gap: 0.85rem;
    justify-content: space-between;
    margin-bottom: 0.85rem;
}

.commitments-dashboard .commit-section-title {
    color: var(--mcm-text-strong);
    font-size: 0.95rem;
    font-weight: 600;
    line-height: 1.2;
}

.commitments-dashboard .commit-kpi-grid {
    display: grid;
    gap: 0.9rem;
    grid-template-columns: repeat(5, minmax(0, 1fr));
}

.commitments-dashboard .commit-kpi-card {
    --commit-color: var(--mcm-text-strong);
    background: var(--mcm-surface);
    border: 1px solid var(--mcm-border);
    border-radius: 12px;
    display: grid;
    gap: 0.5rem;
    min-height: 7.5rem;
    padding: 1.25rem;
}

.commitments-dashboard .commit-kpi-value {
    color: var(--commit-color);
    font-size: 1.9rem;
    font-weight: 600;
    letter-spacing: -0.02em;
    line-height: 1;
}

.commitments-dashboard .commit-kpi-sub { color: var(--mcm-muted); font-size: 0.78rem; }
.commitments-dashboard .is-red { --commit-color: var(--mcm-red); }
.commitments-dashboard .is-amber { --commit-color: var(--mcm-amber); }
.commitments-dashboard .is-blue { --commit-color: var(--mcm-blue); }
.commitments-dashboard .is-green { --commit-color: var(--mcm-green); }
.commitments-dashboard .is-muted { --commit-color: var(--mcm-muted); }

.commitments-dashboard .commit-bucket {
    --bucket-color: var(--mcm-text-strong);
    background: var(--mcm-surface);
    border: 1px solid var(--mcm-border);
    border-radius: 12px;
    overflow: hidden;
}
.commitments-dashboard .commit-bucket.is-red { --bucket-color: var(--mcm-red); }
.commitments-dashboard .commit-bucket.is-amber { --bucket-color: var(--mcm-amber); }
.commitments-dashboard .commit-bucket.is-blue { --bucket-color: var(--mcm-blue); }
.commitments-dashboard .commit-bucket.is-green { --bucket-color: var(--mcm-green); }
.commitments-dashboard .commit-bucket.is-gray { --bucket-color: var(--mcm-muted); }

.commitments-dashboard .commit-bucket-header {
    align-items: center;
    cursor: pointer;
    display: grid;
    gap: 0.9rem;
    grid-template-columns: auto minmax(0, 1fr) auto auto;
    padding: 1rem 1.25rem;
    user-select: none;
}

.commitments-dashboard .commit-bucket-mark {
    background: var(--bucket-color);
    border-radius: 4px;
    height: 2.5rem;
    width: 0.25rem;
}

.commitments-dashboard .commit-count {
    background: color-mix(in srgb, var(--bucket-color) 10%, transparent);
    border-radius: 9999px;
    color: var(--bucket-color);
    display: inline-flex;
    font-size: 0.78rem;
    font-weight: 600;
    justify-content: center;
    min-width: 2rem;
    padding: 0.3rem 0.5rem;
}

.commitments-dashboard .commit-bucket-title { color: var(--mcm-text-strong); font-size: 0.88rem; font-weight: 600; }
.commitments-dashboard .commit-bucket-sub { color: var(--mcm-muted); font-size: 0.78rem; margin-top: 0.15rem; }
.commitments-dashboard .commit-bucket-value { color: var(--mcm-text-strong); font-family: var(--font-mono, monospace); font-size: 0.82rem; font-weight: 600; white-space: nowrap; font-variant-numeric: tabular-nums; }
.commitments-dashboard .commit-chevron { color: var(--mcm-muted); height: 1rem; width: 1rem; }
.commitments-dashboard .commit-chevron.is-open { transform: rotate(180deg); }
.commitments-dashboard .commit-table-wrap { border-top: 1px solid var(--mcm-border); overflow-x: auto; }
.commitments-dashboard .data-table { min-width: 48rem; }
.commitments-dashboard .commit-client { color: var(--mcm-text-strong); font-weight: 600; }
.commitments-dashboard .commit-muted { color: var(--mcm-muted); font-size: 0.78rem; }
.commitments-dashboard .commit-money { color: var(--mcm-text-strong); font-family: var(--font-mono, monospace); font-weight: 600; font-variant-numeric: tabular-nums; }

.commitments-dashboard .commit-badge {
    background: color-mix(in srgb, var(--badge-color, var(--mcm-text-strong)) 10%, transparent);
    border-radius: 9999px;
    color: var(--badge-color, var(--mcm-text-strong));
    display: inline-flex;
    font-size: 0.72rem;
    font-weight: 600;
    padding: 0.2rem 0.5rem;
}

.commitments-dashboard .commit-empty {
    background: var(--mcm-surface);
    border: 1px solid var(--mcm-border);
    border-radius: 12px;
    padding: 3rem 1.5rem;
    text-align: center;
}

.commitments-dashboard .commit-empty svg { color: var(--mcm-soft); height: 3rem; margin: 0 auto 0.85rem; width: 3rem; }

@media (max-width: 1120px) { .commitments-dashboard .commit-kpi-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (max-width: 760px) {
    .commitments-dashboard .commit-hero-meta,
    .commitments-dashboard .commit-filter-grid,
    .commitments-dashboard .commit-kpi-grid { grid-template-columns: 1fr; }
    .commitments-dashboard .commit-section-head { align-items: stretch; flex-direction: column; }
    .commitments-dashboard .commit-bucket-header { grid-template-columns: auto minmax(0, 1fr) auto; }
    .commitments-dashboard .commit-bucket-value { grid-column: 2 / -1; }
}
</style>
@endpush

@php
    $s = $this->summary;
    $b = $this->buckets;
    $totalItems = array_sum(array_map(fn ($bucket) => count($bucket['items']), $b));
    $pendingCount = $s['overdue_count'] + $s['today_count'] + $s['upcoming_count'];
@endphp

<div class="mcm-modern-page commitments-dashboard space-y-5">
    <section class="page-hero mcm-reveal">
        <div>
            <p class="commit-label">Promesas de pago</p>
            <h1>Compromisos de Pago</h1>
            <p>Seguimiento por estado, fecha prometida y asesor responsable.</p>
        </div>
        <div class="commit-hero-meta">
            <div class="commit-hero-stat"><span>Valor pendiente</span><strong>${{ number_format($s['total_pending_value'], 0, ',', '.') }}</strong></div>
            <div class="commit-hero-stat"><span>Compromisos abiertos</span><strong>{{ number_format($pendingCount) }}</strong></div>
        </div>
    </section>

    <section class="filter-bar mcm-reveal">
        <div class="commit-filter-grid">
            <div><p class="filter-label">Asesor</p><select wire:model="advisorId" class="filter-input"><option value="">Todos los asesores</option>@foreach($this->advisorOptions as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach</select></div>
            <p class="commit-filter-copy">Filtra el tablero para revisar vencidos, compromisos del día y promesas futuras por responsable.</p>
            <button wire:click="applyFilter" class="btn-primary"><x-heroicon-o-funnel /> Filtrar</button>
        </div>
    </section>

    <section class="mcm-reveal">
        <div class="commit-section-head">
            <div><div class="commit-section-title">Resumen de compromisos</div><p class="commit-section-copy">Priorización de cartera por urgencia y estado de cumplimiento.</p></div>
            <span class="commit-badge" style="--badge-color: var(--mcm-text-strong);">{{ number_format($totalItems) }} compromisos</span>
        </div>
        <div class="commit-kpi-grid mcm-stagger">
            <article class="commit-kpi-card is-red"><div><span class="commit-label">Vencidos</span><div class="commit-kpi-value">{{ number_format($s['overdue_count']) }}</div></div><div class="commit-kpi-sub">Fecha pasada, sin cumplir.</div></article>
            <article class="commit-kpi-card is-amber"><div><span class="commit-label">Para hoy</span><div class="commit-kpi-value">{{ number_format($s['today_count']) }}</div></div><div class="commit-kpi-sub">Compromisos con vencimiento hoy.</div></article>
            <article class="commit-kpi-card is-blue"><div><span class="commit-label">Próximos</span><div class="commit-kpi-value">{{ number_format($s['upcoming_count']) }}</div></div><div class="commit-kpi-sub">Promesas con fecha futura.</div></article>
            <article class="commit-kpi-card is-green"><div><span class="commit-label">Cumplidos</span><div class="commit-kpi-value">{{ number_format($s['fulfilled_count']) }}</div></div><div class="commit-kpi-sub">Promesas cerradas correctamente.</div></article>
            <article class="commit-kpi-card is-muted"><div><span class="commit-label">Incumplidos</span><div class="commit-kpi-value">{{ number_format($s['broken_count']) }}</div></div><div class="commit-kpi-sub">Compromisos en estado no vigente.</div></article>
        </div>
    </section>

    <section class="space-y-3">
        <div class="commit-section-head"><div><div class="commit-section-title">Bandeja de seguimiento</div><p class="commit-section-copy">Abre cada grupo para revisar cliente, documento, fecha y asesor.</p></div></div>
        @foreach($b as $key => $bucket)
            @if(count($bucket['items']))
                @php
                    $totalVal = collect($bucket['items'])->sum(fn ($item) => (float) ($item['promised_amount'] ?? 0));
                    $bucketClass = match ($bucket['color']) { 'red' => 'is-red', 'amber' => 'is-amber', 'blue' => 'is-blue', 'green' => 'is-green', default => 'is-gray' };
                @endphp
                <article class="commit-bucket {{ $bucketClass }} mcm-reveal" x-data="{ open: {{ in_array($key, ['overdue', 'today']) ? 'true' : 'false' }} }">
                    <header class="commit-bucket-header" @click="open = !open">
                        <div class="commit-bucket-mark"></div>
                        <div><div class="commit-bucket-title">{{ $bucket['label'] }}</div><div class="commit-bucket-sub">{{ count($bucket['items']) }} registros para seguimiento.</div></div>
                        <span class="commit-count">{{ count($bucket['items']) }}</span>
                        <span class="commit-bucket-value">@if($totalVal > 0) ${{ number_format($totalVal, 0, ',', '.') }} @else Sin valor @endif</span>
                        <x-heroicon-o-chevron-down class="commit-chevron" x-bind:class="{ 'is-open': open }" />
                    </header>
                    <div x-show="open" x-transition class="commit-table-wrap">
                        <table class="data-table">
                            <thead><tr><th>Cliente</th><th>NIT</th><th>Documento</th><th>Fecha compromiso</th><th class="text-right">Valor</th><th>Asesor</th></tr></thead>
                            <tbody>
                                @foreach($bucket['items'] as $item)
                                <tr>
                                    <td class="commit-client">{{ $item['client_name'] }}</td>
                                    <td class="commit-muted">{{ $item['nit'] }}</td>
                                    <td class="commit-muted">{{ $item['document_number'] ?? '-' }}</td>
                                    <td>
                                        @php $date = $item['promised_date']; $today = now()->toDateString(); $isPast = $date < $today; @endphp
                                        <span style="color: {{ $isPast && $key === 'overdue' ? 'var(--mcm-red)' : 'var(--mcm-text)' }}; font-weight: {{ $isPast && $key === 'overdue' ? '600' : '400' }};">{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</span>
                                        @if($isPast && $key === 'overdue') <span class="commit-badge" style="--badge-color: var(--mcm-red); margin-left: 0.35rem;">{{ \Carbon\Carbon::parse($date)->diffInDays(now()) }}d</span> @endif
                                    </td>
                                    <td class="text-right commit-money">@if($item['promised_amount']) ${{ number_format($item['promised_amount'], 0, ',', '.') }} @else <span class="commit-muted">-</span> @endif</td>
                                    <td class="commit-muted">{{ $item['advisor_name'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </article>
            @endif
        @endforeach
    </section>

    @if($totalItems === 0)
        <section class="commit-empty mcm-reveal">
            <x-heroicon-o-check-badge />
            <div class="commit-section-title">No hay compromisos de pago registrados</div>
            <p class="commit-section-copy" style="margin-top: 0.35rem;">Los compromisos se crean desde la Bitácora de Gestión.</p>
        </section>
    @endif
</div>

</x-filament-panels::page>
