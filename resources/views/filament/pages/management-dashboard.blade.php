<x-filament-panels::page>

@include('filament.pages.partials.modern-dashboard-styles')

@push('styles')
<style>
.mgmt-dashboard .mgmt-hero-meta {
    display: grid;
    gap: 0.75rem;
    grid-template-columns: repeat(2, minmax(8rem, 1fr));
    min-width: min(100%, 22rem);
}

.mgmt-dashboard .mgmt-hero-stat {
    background: var(--mcm-surface-soft);
    border: 1px solid var(--mcm-border);
    border-radius: 8px;
    min-height: 4.25rem;
    padding: 0.85rem 0.95rem;
}

.mgmt-dashboard .mgmt-hero-stat span,
.mgmt-dashboard .mgmt-mini-label {
    color: var(--mcm-muted);
    display: block;
    font-size: 0.72rem;
    font-weight: 500;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.mgmt-dashboard .mgmt-hero-stat strong {
    color: var(--mcm-text-strong);
    display: block;
    font-size: 1.35rem;
    font-weight: 600;
    letter-spacing: -0.02em;
    line-height: 1.1;
    margin-top: 0.3rem;
}

.mgmt-dashboard .mgmt-filter-grid {
    align-items: end;
    display: grid;
    gap: 0.9rem;
    grid-template-columns: minmax(12rem, 0.35fr) minmax(12rem, 1fr) auto;
}

.mgmt-dashboard .mgmt-filter-note {
    color: var(--mcm-muted);
    font-size: 0.82rem;
    line-height: 1.45;
}

.mgmt-dashboard .mgmt-section-head {
    align-items: end;
    display: flex;
    gap: 0.85rem;
    justify-content: space-between;
    margin-bottom: 0.85rem;
}

.mgmt-dashboard .mgmt-section-title {
    color: var(--mcm-text-strong);
    font-size: 0.95rem;
    font-weight: 600;
    line-height: 1.2;
}

.mgmt-dashboard .mgmt-section-copy {
    color: var(--mcm-muted);
    font-size: 0.82rem;
    margin-top: 0.2rem;
}

.mgmt-dashboard .mgmt-kpi-grid {
    display: grid;
    gap: 0.9rem;
    grid-template-columns: repeat(4, minmax(0, 1fr));
}

.mgmt-dashboard .mgmt-kpi-card {
    background: var(--mcm-surface);
    border: 1px solid var(--mcm-border);
    border-radius: 12px;
    display: grid;
    gap: 0.5rem;
    min-height: 7.5rem;
    padding: 1.25rem;
}


.mgmt-dashboard .mgmt-kpi-label {
    color: var(--mcm-muted);
    font-size: 0.72rem;
    font-weight: 500;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.mgmt-dashboard .mgmt-kpi-value {
    color: var(--mcm-text-strong);
    font-size: 2rem;
    font-weight: 600;
    letter-spacing: -0.02em;
    line-height: 1;
}

.mgmt-dashboard .mgmt-kpi-card.is-green .mgmt-kpi-value { color: var(--mcm-green); }
.mgmt-dashboard .mgmt-kpi-card.is-amber .mgmt-kpi-value { color: var(--mcm-amber); }
.mgmt-dashboard .mgmt-kpi-card.is-red .mgmt-kpi-value { color: var(--mcm-red); }
.mgmt-dashboard .mgmt-kpi-card.is-blue .mgmt-kpi-value { color: var(--mcm-blue); }

.mgmt-dashboard .mgmt-kpi-sub {
    color: var(--mcm-muted);
    font-size: 0.78rem;
}

.mgmt-dashboard .mgmt-content-grid {
    display: grid;
    gap: 1rem;
    grid-template-columns: minmax(0, 1.35fr) minmax(19rem, 0.65fr);
    align-items: stretch;
}

.mgmt-dashboard .mgmt-chart-card,
.mgmt-dashboard .mgmt-panel-card,
.mgmt-dashboard .mgmt-table-card {
    background: var(--mcm-surface);
    border: 1px solid var(--mcm-border);
    border-radius: 12px;
    padding: 1.5rem;
}

.mgmt-dashboard .mgmt-chart-card { min-height: 24rem; }
.mgmt-dashboard .mgmt-chart-frame { height: 18rem; position: relative; }

.mgmt-dashboard .mgmt-health-list { display: grid; gap: 0.8rem; margin-top: 1rem; }
.mgmt-dashboard .mgmt-health-row { align-items: center; display: grid; gap: 0.75rem; grid-template-columns: minmax(0, 1fr) auto; }
.mgmt-dashboard .mgmt-health-value { color: var(--mcm-text-strong); font-size: 1rem; font-weight: 600; }

.mgmt-dashboard .mgmt-bar { background: var(--mcm-surface-strong); border-radius: 999px; height: 0.35rem; overflow: hidden; margin-top: 0.45rem; }
.mgmt-dashboard .mgmt-bar-fill { background: var(--bar-color, var(--mcm-text-strong)); border-radius: 999px; height: 100%; min-width: 0.25rem; }

.mgmt-dashboard .mgmt-badge {
    background: var(--mcm-accent-soft);
    border-radius: 9999px;
    color: var(--mcm-text-strong);
    display: inline-flex;
    font-size: 0.72rem;
    font-weight: 600;
    padding: 0.25rem 0.6rem;
    letter-spacing: 0.03em;
}

.mgmt-dashboard .mgmt-table-wrap { overflow-x: auto; }
.mgmt-dashboard .data-table { min-width: 46rem; }

.mgmt-dashboard .advisor-name { color: var(--mcm-text-strong); font-weight: 600; }
.mgmt-dashboard .money-value { font-family: var(--font-mono, ui-monospace, monospace); font-weight: 600; font-variant-numeric: tabular-nums; }
.mgmt-dashboard .empty-row { color: var(--mcm-muted); padding: 2.5rem 1rem; text-align: center; }

@media (max-width: 1120px) {
    .mgmt-dashboard .mgmt-kpi-grid,
    .mgmt-dashboard .mgmt-content-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .mgmt-dashboard .mgmt-chart-card { grid-column: 1 / -1; }
}

@media (max-width: 760px) {
    .mgmt-dashboard .mgmt-hero-meta,
    .mgmt-dashboard .mgmt-filter-grid,
    .mgmt-dashboard .mgmt-kpi-grid,
    .mgmt-dashboard .mgmt-content-grid { grid-template-columns: 1fr; }
    .mgmt-dashboard .mgmt-section-head { align-items: stretch; flex-direction: column; }
    .mgmt-dashboard .mgmt-chart-frame { height: 15rem; }
}
</style>
@endpush

@php
    $k = $this->periodKpis;
    $promiseRate = $k['promises_month'] > 0 ? round($k['fulfilled_month'] / $k['promises_month'] * 100) : 0;
    $brokenRate = $k['promises_month'] > 0 ? round($k['broken_month'] / $k['promises_month'] * 100) : 0;
    $clientCoverage = ($k['clients_managed'] + $k['clients_unmanaged']) > 0
        ? round($k['clients_managed'] / ($k['clients_managed'] + $k['clients_unmanaged']) * 100)
        : 0;
@endphp

<div class="mcm-modern-page mgmt-dashboard space-y-5" x-data="mgmtDashboard(@js($this->trendChart))">
    <section class="page-hero mcm-reveal">
        <div>
            <p class="mgmt-mini-label">Operación de cobro</p>
            <h1>Dashboard de Gestión</h1>
            <p>Actividad de asesores, promesas y cobertura de clientes para tomar decisiones rapidas.</p>
        </div>
        <div class="mgmt-hero-meta">
            <div class="mgmt-hero-stat"><span>Gestiones mes</span><strong>{{ number_format($k['month']) }}</strong></div>
            <div class="mgmt-hero-stat"><span>Cumplimiento</span><strong>{{ $promiseRate }}%</strong></div>
        </div>
    </section>

    <section class="filter-bar mcm-reveal">
        <div class="mgmt-filter-grid">
            <div>
                <p class="filter-label">Ventana de tendencia</p>
                <select wire:model="trendDays" class="filter-input">
                    <option value="7">Últimos 7 días</option>
                    <option value="30">Últimos 30 días</option>
                    <option value="90">Últimos 90 días</option>
                </select>
            </div>
            <p class="mgmt-filter-note">Actualiza la gráfica sin salir del tablero. Los indicadores principales mantienen la lectura operativa del período actual.</p>
            <button wire:click="applyFilter" class="btn-primary"><x-heroicon-o-arrow-path /> Actualizar</button>
        </div>
    </section>

    <section class="mcm-reveal">
        <div class="mgmt-section-head">
            <div>
                <div class="mgmt-section-title">Resumen de actividad</div>
                <p class="mgmt-section-copy">Lectura rápida de volumen, promesas y clientes sin gestión reciente.</p>
            </div>
            <span class="mgmt-badge">Últimos 30 días</span>
        </div>
        <div class="mgmt-kpi-grid mcm-stagger">
            <article class="mgmt-kpi-card is-blue"><div><div class="mgmt-kpi-label">Hoy</div><div class="mgmt-kpi-value">{{ number_format($k['today']) }}</div></div><div class="mgmt-kpi-sub">Gestiones registradas en el día.</div></article>
            <article class="mgmt-kpi-card is-blue"><div><div class="mgmt-kpi-label">Semana</div><div class="mgmt-kpi-value">{{ number_format($k['week']) }}</div></div><div class="mgmt-kpi-sub">Actividad acumulada de los últimos 7 días.</div></article>
            <article class="mgmt-kpi-card is-amber"><div><div class="mgmt-kpi-label">Promesas</div><div class="mgmt-kpi-value">{{ number_format($k['promises_month']) }}</div></div><div class="mgmt-kpi-sub">{{ $promiseRate }}% cumplidas en el período.</div></article>
            <article class="mgmt-kpi-card {{ $k['clients_unmanaged'] > 20 ? 'is-red' : 'is-amber' }}"><div><div class="mgmt-kpi-label">Sin gestión 7d</div><div class="mgmt-kpi-value">{{ number_format($k['clients_unmanaged']) }}</div></div><div class="mgmt-kpi-sub">Clientes activos pendientes de contacto.</div></article>
        </div>
    </section>

    <section class="mgmt-content-grid">
        <article class="mgmt-chart-card mcm-reveal">
            <div class="mgmt-section-head">
                <div><div class="mgmt-section-title">Tendencia de gestiones</div><p class="mgmt-section-copy">Evolución diaria durante los últimos {{ $trendDays }} días.</p></div>
                <span class="mgmt-badge">{{ $trendDays }} días</span>
            </div>
            <div class="mgmt-chart-frame"><canvas x-ref="trendChart"></canvas></div>
        </article>

        <aside class="mgmt-panel-card mcm-reveal">
            <div class="mgmt-section-title">Salud de promesas</div>
            <p class="mgmt-section-copy">Indicadores clave del seguimiento comercial del mes.</p>
            <div class="mgmt-health-list">
                <div class="mgmt-health-row"><div><span class="mgmt-mini-label">Cumplidas</span><div class="mgmt-bar"><div class="mgmt-bar-fill" style="--bar-color: var(--mcm-green); width: {{ min($promiseRate, 100) }}%"></div></div></div><span class="mgmt-health-value">{{ number_format($k['fulfilled_month']) }}</span></div>
                <div class="mgmt-health-row"><div><span class="mgmt-mini-label">Incumplidas</span><div class="mgmt-bar"><div class="mgmt-bar-fill" style="--bar-color: var(--mcm-red); width: {{ min($brokenRate, 100) }}%"></div></div></div><span class="mgmt-health-value">{{ number_format($k['broken_month']) }}</span></div>
                <div class="mgmt-health-row"><div><span class="mgmt-mini-label">Clientes gestionados 7d</span><div class="mgmt-bar"><div class="mgmt-bar-fill" style="--bar-color: var(--mcm-text-strong); width: {{ min($clientCoverage, 100) }}%"></div></div></div><span class="mgmt-health-value">{{ number_format($k['clients_managed']) }}</span></div>
            </div>
        </aside>
    </section>

    <section class="mgmt-table-card mcm-reveal">
        <div class="mgmt-section-head">
            <div><div class="mgmt-section-title">Rendimiento por asesor</div><p class="mgmt-section-copy">Gestiones, promesas y valor prometido en los últimos 30 días.</p></div>
        </div>
        <div class="mgmt-table-wrap">
            <table class="data-table">
                <thead><tr><th>Asesor</th><th class="text-right">Gestiones</th><th class="text-right">Promesas</th><th class="text-right">Cumplidas</th><th class="text-right">Incumplidas</th><th class="text-right">% Cumplimiento</th><th class="text-right">Valor prometido</th></tr></thead>
                <tbody>
                    @forelse($this->advisorStats as $row)
                    @php $row = (object) $row; $pct = $row->promises > 0 ? round($row->fulfilled / $row->promises * 100) : 0; @endphp
                    <tr>
                        <td class="advisor-name">{{ $row->name }}</td>
                        <td class="text-right">{{ number_format($row->total_actions) }}</td>
                        <td class="text-right">{{ number_format($row->promises) }}</td>
                        <td class="text-right" style="color: var(--mcm-green);">{{ number_format($row->fulfilled) }}</td>
                        <td class="text-right" style="color: var(--mcm-red);">{{ number_format($row->broken) }}</td>
                        <td class="text-right">
                            <div style="display:flex;align-items:center;justify-content:flex-end;gap:0.5rem;">
                                <div class="mgmt-bar" style="width: 4.4rem; margin-top: 0;"><div class="mgmt-bar-fill" style="--bar-color: {{ $pct >= 70 ? 'var(--mcm-green)' : ($pct >= 40 ? 'var(--mcm-amber)' : 'var(--mcm-red)') }}; width: {{ min($pct, 100) }}%"></div></div>
                                <span style="color: {{ $pct >= 70 ? 'var(--mcm-green)' : ($pct >= 40 ? 'var(--mcm-amber)' : 'var(--mcm-red)') }}; font-weight: 600;">{{ $pct }}%</span>
                            </div>
                        </td>
                        <td class="text-right money-value">${{ number_format($row->recovery_value, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="empty-row">Sin actividad en los últimos 30 días.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('mgmtDashboard', (chart) => ({
        chart, _chart: null, _themeObserver: null,
        init() {
            this.$nextTick(() => this.build(chart));
            this.$el.addEventListener('mgmt-chart-updated', (e) => { this.chart = e.detail.chart; this.build(this.chart); });
            this._themeObserver = new MutationObserver(() => this.build(this.chart));
            this._themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
        },
        chartTheme() {
            const isDark = document.documentElement.classList.contains('dark');
            return { text: isDark ? '#94A3B8' : '#6B7280', grid: isDark ? 'rgba(148,163,184,.12)' : 'rgba(107,114,128,.1)' };
        },
        destroy() { if (this._themeObserver) this._themeObserver.disconnect(); if (this._chart) this._chart.destroy(); },
        build(data) {
            const canvas = this.$refs.trendChart;
            if (!canvas) return;
            const t = this.chartTheme();
            if (this._chart) this._chart.destroy();
            this._chart = new Chart(canvas, {
                type: 'line', data,
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    elements: { point: { radius: 3, hoverRadius: 5 } },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: t.text, font: { size: 10 } } },
                        y: { grid: { color: t.grid }, ticks: { color: t.text, font: { size: 10 }, stepSize: 1 } }
                    }
                }
            });
        }
    }));
});
</script>
@endpush

</x-filament-panels::page>
