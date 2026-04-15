<x-filament-panels::page>

@include('filament.pages.partials.modern-dashboard-styles')

@push('styles')
<style>
.pmc-dashboard .tab-group {
    display: flex;
    gap: 0.25rem;
    background: var(--mcm-surface-strong);
    border-radius: 8px;
    padding: 0.25rem;
}
</style>
@endpush

@php
    $k = $this->kpis;
    $exportUrl = route('admin.exports.portfolio', ['period' => $this->period]);
@endphp

<div class="mcm-modern-page pmc-dashboard space-y-5" x-data="pmcDashboard(@js($this->charts))">

    <div class="page-hero mcm-reveal">
        <div>
            <h1>Dashboard de Gestión de Cartera</h1>
            <p>{{ $k['total_clients'] }} clientes · {{ number_format($k['total_docs']) }} documentos activos</p>
        </div>
        <div style="display:flex;align-items:center;gap:0.75rem;">
            <div class="tab-group" x-data="{ tab: 'executive' }">
                <button @click="tab='executive'" :class="tab==='executive' ? 'tab-btn active' : 'tab-btn'">Vista Ejecutiva</button>
                <button @click="tab='operational'" :class="tab==='operational' ? 'tab-btn active' : 'tab-btn'">Vista Operativa</button>
            </div>
            <a href="{{ $exportUrl }}" target="_blank" class="btn-primary">
                <x-heroicon-o-arrow-down-tray style="width:1rem;height:1rem;"/> Descargar XLSX
            </a>
        </div>
    </div>

    <div class="filter-bar mcm-reveal">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(10rem,1fr));gap:0.75rem;margin-bottom:0.75rem;">
            <div><p class="filter-label">Período</p><select wire:model="period" class="filter-input"><option value="">Más reciente</option>@foreach($this->periodOptions as $v => $l)<option value="{{ $v }}">{{ $v }}</option>@endforeach</select></div>
            <div><p class="filter-label">UEN</p><select wire:model="uen" class="filter-input"><option value="">Todas</option>@foreach($this->uenOptions as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select></div>
            <div><p class="filter-label">Regional</p><select wire:model="regional" class="filter-input"><option value="">Todas</option>@foreach($this->regionalOptions as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select></div>
        </div>
        <div style="display:flex;gap:0.5rem;">
            <button wire:click="runFilter" class="btn-primary"><x-heroicon-o-funnel style="width:1rem;height:1rem;"/>Aplicar</button>
            <button wire:click="resetFilters" class="btn-ghost"><x-heroicon-o-x-mark style="width:1rem;height:1rem;"/>Limpiar</button>
        </div>
    </div>

    @if($k['portfolio_empty'])
    <div class="chart-card"><div class="empty-state"><x-heroicon-o-inbox style="width:3rem;height:3rem;color:var(--mcm-soft);"/><p style="font-size:0.88rem;font-weight:600;">Sin datos para el período seleccionado</p></div></div>
    @else

    <div x-show="tab==='executive'" x-transition class="space-y-4">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(11rem,1fr));gap:0.75rem;" class="mcm-stagger">
            <div class="kpi-card"><p class="kpi-label">Cartera Total</p><p class="kpi-value">${{ number_format($k['portfolio_total'], 0, ',', '.') }}</p><p class="kpi-sub">{{ number_format($k['total_docs']) }} documentos</p></div>
            <div class="kpi-card"><p class="kpi-label">% Vencida</p><p class="kpi-value {{ $k['overdue_rate'] > 30 ? 'text-red-500' : ($k['overdue_rate'] > 15 ? 'text-amber-500' : 'text-emerald-500') }}">{{ $k['overdue_rate'] }}%</p><p class="kpi-sub">${{ number_format($k['overdue_total'], 0, ',', '.') }}</p></div>
            <div class="kpi-card"><p class="kpi-label">Crítica &gt;90d</p><p class="kpi-value {{ $k['critical_rate'] > 15 ? 'text-red-500' : 'text-amber-500' }}">{{ $k['critical_rate'] }}%</p><p class="kpi-sub">${{ number_format($k['critical_amount'], 0, ',', '.') }}</p></div>
            <div class="kpi-card"><p class="kpi-label">Rotación (DSO)</p>@if($k['dso'])<p class="kpi-value">{{ $k['dso'] }}<span style="font-size:0.82rem;font-weight:500;color:var(--mcm-muted);">d</span></p>@else<p class="kpi-value" style="color:var(--mcm-soft);">—</p>@endif<p class="kpi-sub">Días promedio de rotación</p></div>
            <div class="kpi-card"><p class="kpi-label">Recaudo Período</p><p class="kpi-value" style="color:var(--mcm-green);">${{ number_format($k['collected_this_period'], 0, ',', '.') }}</p><p class="kpi-sub">{{ $k['clients_overdue'] }} clientes en mora</p></div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(20rem,1fr));gap:1rem;">
            <div class="chart-card mcm-reveal">
                <p class="chart-title">Distribución por Antigüedad de Mora</p>
                <div style="position:relative;height:13rem;"><canvas x-ref="pmcAging"></canvas></div>
            </div>
            <div class="chart-card mcm-reveal">
                <p class="chart-title">Resumen de Compromisos de Pago</p>
                <div style="display:flex;align-items:center;justify-content:space-around;height:13rem;">
                    <div style="text-align:center;display:grid;gap:0.5rem;">
                        <div class="promise-circle" style="background:var(--mcm-amber-bg);color:var(--mcm-amber);margin:0 auto;">{{ $k['promises_pending'] }}</div>
                        <p style="font-size:0.72rem;font-weight:600;color:var(--mcm-amber);">Pendientes</p>
                        <p style="font-size:0.72rem;color:var(--mcm-muted);">${{ number_format($k['promises_value'], 0, ',', '.') }}</p>
                    </div>
                    <div style="text-align:center;display:grid;gap:0.5rem;">
                        <div class="promise-circle" style="background:var(--mcm-green-bg);color:var(--mcm-green);margin:0 auto;">{{ $k['promises_fulfilled'] }}</div>
                        <p style="font-size:0.72rem;font-weight:600;color:var(--mcm-green);">Cumplidos</p>
                    </div>
                    <div style="text-align:center;display:grid;gap:0.5rem;">
                        <div class="promise-circle" style="background:var(--mcm-red-bg);color:var(--mcm-red);margin:0 auto;">{{ $k['promises_broken'] }}</div>
                        <p style="font-size:0.72rem;font-weight:600;color:var(--mcm-red);">Incumplidos</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="chart-card mcm-reveal">
            <p class="chart-title">Concentración de Cartera — Top 10 Clientes</p>
            <div style="position:relative;height:16rem;"><canvas x-ref="pmcPareto"></canvas></div>
        </div>
    </div>

    <div x-show="tab==='operational'" x-transition class="space-y-5">
        <div class="chart-card mcm-reveal">
            <p class="dash-section-title">Por Cliente</p>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead><tr><th>Cliente</th><th>NIT</th><th class="text-right">Docs</th><th class="text-right">Saldo Total</th><th class="text-right">Saldo Vencido</th><th class="text-right">Mora Máx.</th><th></th></tr></thead>
                    <tbody>
                        @forelse($this->byClient as $row)
                        <tr>
                            <td style="font-weight:600;color:var(--mcm-text-strong);">{{ $row->name ?? $row['name'] }}</td>
                            <td style="color:var(--mcm-muted);">{{ $row->nit ?? $row['nit'] }}</td>
                            <td class="text-right">{{ $row->total_docs ?? $row['total_docs'] }}</td>
                            <td class="text-right money-value">${{ number_format($row->total_balance ?? $row['total_balance'], 0, ',', '.') }}</td>
                            <td class="text-right money-value" style="{{ ($row->overdue_balance ?? $row['overdue_balance']) > 0 ? 'color:var(--mcm-red);' : 'color:var(--mcm-muted);' }}">${{ number_format($row->overdue_balance ?? $row['overdue_balance'], 0, ',', '.') }}</td>
                            <td class="text-right">
                                @php $d = $row->days_overdue_max ?? $row['days_overdue_max']; @endphp
                                @if($d > 90)<span class="badge-pill badge-red">{{ $d }}d</span>
                                @elseif($d > 30)<span class="badge-pill badge-amber">{{ $d }}d</span>
                                @elseif($d > 0)<span class="badge-pill badge-green">{{ $d }}d</span>
                                @else<span class="badge-pill badge-gray">Corriente</span>
                                @endif
                            </td>
                            <td><a href="{{ route('filament.admin.resources.clients.view', ['record' => $row->id ?? $row['id']]) }}" style="font-size:0.78rem;font-weight:600;color:var(--mcm-text-strong);text-decoration:none;">Ver perfil →</a></td>
                        </tr>
                        @empty
                        <tr><td colspan="7" style="text-align:center;color:var(--mcm-muted);padding:2rem;">Sin datos</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="chart-card mcm-reveal">
            <p class="dash-section-title">Documentos en Mora (Top 100 por días vencidos)</p>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead><tr><th>Documento</th><th>Cliente</th><th>Vencimiento</th><th class="text-right">Días Mora</th><th class="text-right">Saldo</th><th>Riesgo</th></tr></thead>
                    <tbody>
                        @forelse($this->byDocument as $row)
                        @php
                            $row = (object)$row;
                            $dias = $row->days_overdue ?? 0;
                            $riskColors = ['critical'=>'badge-red','high'=>'badge-red','medium'=>'badge-amber','low'=>'badge-amber','normal'=>'badge-gray'];
                            $riskLabels = ['critical'=>'Crítico','high'=>'Alto','medium'=>'Medio','low'=>'Bajo','normal'=>'Normal'];
                        @endphp
                        <tr>
                            <td style="font-family:var(--font-mono,monospace);color:var(--mcm-muted);font-size:0.78rem;">{{ $row->document_number }}</td>
                            <td>{{ $row->client_name }}</td>
                            <td style="color:var(--mcm-muted);">{{ $row->due_date ? \Carbon\Carbon::parse($row->due_date)->format('d/m/Y') : '—' }}</td>
                            <td class="text-right">
                                @if($dias > 90)<span class="badge-pill badge-red">{{ $dias }}d</span>
                                @elseif($dias > 60)<span class="badge-pill badge-amber">{{ $dias }}d</span>
                                @elseif($dias > 30)<span class="badge-pill badge-amber">{{ $dias }}d</span>
                                @else<span class="badge-pill badge-green">{{ $dias }}d</span>
                                @endif
                            </td>
                            <td class="text-right money-value">${{ number_format($row->pending_amount, 0, ',', '.') }}</td>
                            <td><span class="badge-pill {{ $riskColors[$row->risk_level] ?? 'badge-gray' }}">{{ $riskLabels[$row->risk_level] ?? $row->risk_level }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="6" style="text-align:center;color:var(--mcm-muted);padding:2rem;">Sin documentos en mora</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="chart-card mcm-reveal">
            <p class="dash-section-title">Actividad Reciente de Gestión</p>
            @if(count($this->recentLogs))
            <div>
                @foreach($this->recentLogs as $log)
                @php
                    $log = (object)$log;
                    $typeColors = ['Llamada'=>'background:var(--mcm-blue);','Correo'=>'background:#8B5CF6;','Visita'=>'background:var(--mcm-green);','Acuerdo'=>'background:var(--mcm-amber);','Jurídico'=>'background:var(--mcm-red);'];
                    $dot = $typeColors[$log->type_label] ?? 'background:var(--mcm-muted);';
                @endphp
                <div style="display:flex;gap:0.75rem;padding:0.65rem 0;border-bottom:1px solid var(--mcm-border);">
                    <div class="timeline-dot" style="{{ $dot }}"></div>
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:0.5rem;">
                            <p style="font-size:0.82rem;font-weight:600;color:var(--mcm-text-strong);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                {{ $log->client_name }}
                                @if($log->document_number)<span style="font-weight:400;color:var(--mcm-muted);">· {{ $log->document_number }}</span>@endif
                            </p>
                            <span style="font-size:0.72rem;color:var(--mcm-muted);flex-shrink:0;">{{ \Carbon\Carbon::parse($log->contact_date)->format('d/m/Y') }}</span>
                        </div>
                        <p style="font-size:0.72rem;color:var(--mcm-muted);margin-top:0.15rem;">
                            <span style="font-weight:600;">{{ $log->type_label }}</span> · {{ $log->result_label }}
                            @if($log->advisor_name !== '—') · <span>{{ $log->advisor_name }}</span> @endif
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="empty-state" style="padding:2rem;"><x-heroicon-o-clock style="width:2.5rem;height:2.5rem;color:var(--mcm-soft);"/><p style="font-size:0.82rem;">Sin actividad registrada</p></div>
            @endif
        </div>
    </div>

    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('pmcDashboard', (charts) => ({
        tab: 'executive', charts, _charts: {}, _themeObserver: null,
        init() {
            this.$nextTick(() => this.buildAll(charts));
            this.$el.addEventListener('pmcharts-updated', (e) => { this.charts = e.detail.charts; this.buildAll(this.charts); });
            this._themeObserver = new MutationObserver(() => this.buildAll(this.charts));
            this._themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
        },
        chartTheme() {
            const isDark = document.documentElement.classList.contains('dark');
            return { text: isDark ? '#94A3B8' : '#6B7280', grid: isDark ? 'rgba(148,163,184,.12)' : 'rgba(107,114,128,.1)' };
        },
        destroy() { if (this._themeObserver) this._themeObserver.disconnect(); Object.values(this._charts).forEach(c => c.destroy()); },
        buildAll(data) { this.buildDoughnut('pmcAging', data.aging); this.buildPareto('pmcPareto', data.pareto); },
        buildDoughnut(ref, data) {
            const canvas = this.$refs[ref]; if (!canvas) return; const t = this.chartTheme();
            if (this._charts[ref]) this._charts[ref].destroy();
            this._charts[ref] = new Chart(canvas, { type: 'doughnut', data, options: { responsive: true, maintainAspectRatio: false, cutout: '60%', plugins: { legend: { position: 'right', labels: { boxWidth: 10, color: t.text, font: { size: 11 } } }, tooltip: { callbacks: { label: ctx => ` $${ctx.raw.toLocaleString('es-CO')}` } } } } });
        },
        buildPareto(ref, data) {
            const canvas = this.$refs[ref]; if (!canvas) return; const t = this.chartTheme();
            if (this._charts[ref]) this._charts[ref].destroy();
            this._charts[ref] = new Chart(canvas, { data, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, color: t.text, font: { size: 11 } } } }, scales: { x: { grid: { display: false }, ticks: { color: t.text, font: { size: 9 }, maxRotation: 30 } }, y: { position: 'left', grid: { color: t.grid }, ticks: { color: t.text, callback: v => '$'+(v/1e6).toFixed(1)+'M', font: { size: 10 } } }, y1: { position: 'right', grid: { display: false }, ticks: { color: t.text, callback: v => v+'%', font: { size: 10 } }, min: 0, max: 100 } } } });
        },
    }));
});
</script>
@endpush

</x-filament-panels::page>
