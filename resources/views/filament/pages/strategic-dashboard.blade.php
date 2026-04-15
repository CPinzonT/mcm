<x-filament-panels::page>

@include('filament.pages.partials.modern-dashboard-styles')

@push('styles')
<style>
/* ── Variables de página ─────────────────────────────── */
.sd-page { --sd-header-h: 3.25rem; }

/* ── Header PBI ──────────────────────────────────────── */
.sd-header {
    display: flex;
    align-items: center;
    gap: 0;
    background: var(--mcm-surface);
    border: 1px solid var(--mcm-border);
    border-radius: 12px;
    padding: 0 1rem;
    min-height: var(--sd-header-h);
    box-shadow: var(--mcm-shadow-soft);
    overflow: hidden;
}
.sd-logo {
    font-size: 1rem; font-weight: 900; letter-spacing: .03em;
    color: var(--mcm-accent); padding-right: 1rem; flex-shrink: 0;
    line-height: 1;
}
.sd-logo small { display: block; font-size: .55rem; font-weight: 500; letter-spacing: .08em; color: var(--mcm-muted); text-transform: uppercase; }
.sd-hdiv {
    width: 1px; background: var(--mcm-border); align-self: stretch;
    margin: .5rem .75rem;
}
.sd-hkpi {
    display: flex; flex-direction: column; padding: .15rem .75rem; flex-shrink: 0;
}
.sd-hkpi-val { font-size: 1.1rem; font-weight: 800; color: var(--mcm-text); line-height: 1; }
.sd-hkpi-label { font-size: .62rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--mcm-muted); }
.sd-period-wrap { display: flex; align-items: center; gap: .4rem; margin-left: auto; flex-shrink: 0; }
.sd-period-wrap label { font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: var(--mcm-muted); }
.sd-period-select {
    padding: .3rem .6rem; border-radius: 6px;
    border: 1px solid var(--mcm-border); background: var(--mcm-surface-soft);
    color: var(--mcm-text); font-size: .8rem; font-weight: 600; cursor: pointer;
    outline: none;
}
.sd-period-select:focus { border-color: var(--mcm-accent); }
.sd-haction {
    display: flex; align-items: center; gap: .35rem;
    padding: .3rem .5rem; border-radius: 6px;
    background: transparent; border: 1px solid var(--mcm-border);
    color: var(--mcm-muted); cursor: pointer; font-size: .75rem; font-weight: 600;
    text-decoration: none; margin-left: .4rem; flex-shrink: 0;
}
.sd-haction:hover { border-color: var(--mcm-accent); color: var(--mcm-accent); background: var(--mcm-accent-soft); }
.sd-haction svg { width: 14px; height: 14px; }

/* ── Filter bar ──────────────────────────────────────── */
.sd-filter-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: .6rem;
    background: var(--mcm-surface);
    border: 1px solid var(--mcm-border);
    border-radius: 12px;
    padding: .75rem 1rem;
    box-shadow: var(--mcm-shadow-soft);
}
.sd-filter-col { display: flex; flex-direction: column; gap: .2rem; min-width: 8rem; flex: 1; }
.sd-filter-col label { font-size: .67rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--mcm-muted); }
.sd-filter-input {
    padding: .35rem .6rem; border-radius: 7px;
    border: 1px solid var(--mcm-border); background: var(--mcm-surface-soft);
    color: var(--mcm-text); font-size: .8rem; outline: none;
    appearance: none; -webkit-appearance: none;
}
.sd-filter-input:focus { border-color: var(--mcm-accent); box-shadow: 0 0 0 2px color-mix(in srgb,var(--mcm-accent) 15%,transparent); }
.sd-filter-actions { display: flex; align-items: flex-end; gap: .4rem; flex-shrink: 0; }

/* ── Compare toggle button ───────────────────────────── */
.sd-compare-btn {
    padding: .35rem .85rem; border-radius: 7px; font-size: .78rem; font-weight: 600;
    border: 1.5px solid var(--mcm-border); background: var(--mcm-surface-soft);
    color: var(--mcm-muted); cursor: pointer;
}
.sd-compare-btn.active { border-color: var(--mcm-accent); background: var(--mcm-accent); color: #fff; }

/* ── Compare panel ───────────────────────────────────── */
.sd-compare-panel {
    background: var(--mcm-surface);
    border: 1px solid var(--mcm-border);
    border-left: 3px solid var(--mcm-accent);
    border-radius: 12px;
    padding: 1rem 1.25rem;
    box-shadow: var(--mcm-shadow-soft);
}
.sd-compare-panel-title { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--mcm-muted); margin-bottom: .75rem; }
.sd-compare-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(9rem, 1fr)); gap: .65rem; }
.sd-compare-type-tabs { display: flex; gap: .4rem; flex-wrap: wrap; margin-bottom: .75rem; }
.sd-compare-type-tab {
    padding: .3rem .7rem; border-radius: 6px; font-size: .75rem; font-weight: 600;
    border: 1px solid var(--mcm-border); background: var(--mcm-surface-soft);
    color: var(--mcm-muted); cursor: pointer;
}
.sd-compare-type-tab.active { background: var(--mcm-accent); border-color: var(--mcm-accent); color: #fff; }

/* ── KPI strip ───────────────────────────────────────── */
.sd-kpi-strip {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: .6rem;
}
@media (max-width: 1300px) { .sd-kpi-strip { grid-template-columns: repeat(4, 1fr); } }
@media (max-width: 860px)  { .sd-kpi-strip { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 600px)  { .sd-kpi-strip { grid-template-columns: repeat(2, 1fr); } }
.sd-kpi {
    background: var(--mcm-surface); border: 1px solid var(--mcm-border);
    border-radius: 10px; padding: .7rem .85rem;
    box-shadow: var(--mcm-shadow-soft);
}
.sd-kpi-label { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--mcm-muted); margin-bottom: .2rem; }
.sd-kpi-value { font-size: 1.1rem; font-weight: 800; color: var(--mcm-text); line-height: 1; }
.sd-kpi-sub { font-size: .67rem; color: var(--mcm-muted); margin-top: .2rem; }
.sd-kpi-value.c-green  { color: var(--mcm-green); }
.sd-kpi-value.c-amber  { color: var(--mcm-amber); }
.sd-kpi-value.c-red    { color: var(--mcm-red); }
.sd-kpi-value.c-blue   { color: var(--mcm-accent); }

/* ── Score ring ──────────────────────────────────────── */
.sd-score-kpi {
    background: var(--mcm-surface); border: 1px solid var(--mcm-border);
    border-radius: 10px; padding: .7rem .85rem;
    box-shadow: var(--mcm-shadow-soft);
    display: flex; align-items: center; gap: .6rem;
}
.sd-score-kpi svg { flex-shrink: 0; }

/* ── Channel / Cliente checklist panels ──────────────── */
.sd-checklist-bar {
    display: flex; flex-wrap: wrap; gap: .5rem 1.5rem;
    background: var(--mcm-surface); border: 1px solid var(--mcm-border);
    border-radius: 12px; padding: .75rem 1rem;
    box-shadow: var(--mcm-shadow-soft);
    align-items: flex-start;
}
.sd-checklist-section { min-width: 10rem; }
.sd-checklist-title { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--mcm-muted); margin-bottom: .4rem; }
.sd-checklist-items { display: flex; flex-direction: column; gap: .2rem; max-height: 10rem; overflow-y: auto; }
.sd-check-item { display: flex; align-items: center; gap: .4rem; font-size: .78rem; color: var(--mcm-text); cursor: pointer; padding: .1rem 0; }
.sd-check-item input[type=checkbox] { accent-color: var(--mcm-accent); cursor: pointer; }
.sd-filter-search {
    width: 100%; padding: .3rem .6rem; border-radius: 6px;
    border: 1px solid var(--mcm-border); background: var(--mcm-surface-soft);
    color: var(--mcm-text); font-size: .78rem; outline: none; margin-bottom: .35rem;
}
.sd-filter-search:focus { border-color: var(--mcm-accent); }

/* ── Charts grid ─────────────────────────────────────── */
.sd-charts-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: .85rem;
}
@media (max-width: 860px) { .sd-charts-grid { grid-template-columns: 1fr; } }
.sd-chart-card {
    background: var(--mcm-surface); border: 1px solid var(--mcm-border);
    border-radius: 12px; padding: 1rem;
    box-shadow: var(--mcm-shadow-soft);
}
.sd-chart-card.full { grid-column: 1 / -1; }
.sd-chart-title { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--mcm-muted); margin-bottom: .75rem; }
.sd-chart-canvas { height: 15rem; position: relative; }
.sd-chart-canvas.tall { height: 18rem; }

/* ── Comparison KPI table ────────────────────────────── */
.sd-compare-kpis {
    display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;
    background: var(--mcm-surface); border: 1px solid var(--mcm-border);
    border-radius: 12px; padding: 1rem 1.25rem;
    box-shadow: var(--mcm-shadow-soft);
}
.sd-compare-side-title { font-size: .82rem; font-weight: 700; color: var(--mcm-text); border-bottom: 2px solid var(--mcm-accent); padding-bottom: .4rem; margin-bottom: .6rem; }
.sd-compare-row { display: flex; justify-content: space-between; align-items: center; padding: .3rem 0; border-bottom: 1px solid var(--mcm-border); font-size: .78rem; }
.sd-compare-row:last-child { border-bottom: none; }
.sd-compare-row-label { color: var(--mcm-muted); }
.sd-compare-row-val { font-weight: 700; color: var(--mcm-text); display: flex; align-items: center; gap: .4rem; }
.delta-up   { color: var(--mcm-green); font-size: .7rem; font-weight: 700; }
.delta-down { color: var(--mcm-red); font-size: .7rem; font-weight: 700; }
.delta-flat { color: var(--mcm-muted); font-size: .7rem; }

/* ── Empty state ─────────────────────────────────────── */
.sd-empty { text-align: center; padding: 3rem 1rem; color: var(--mcm-muted); }
.sd-empty svg { width: 3rem; height: 3rem; margin: 0 auto .75rem; opacity: .3; display: block; }

/* ── Section title ───────────────────────────────────── */
.sd-section-title { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: var(--mcm-muted); padding: .1rem 0; }

/* ── Score inline ────────────────────────────────────── */
.sd-score-wrap { display: flex; align-items: center; gap: .5rem; }
</style>
@endpush

@php
    $k = $this->kpis;
    $critClass  = $k['critical_rate'] > 20 ? 'c-red'   : ($k['critical_rate'] > 10 ? 'c-amber' : 'c-green');
    $ismClass   = $k['ism'] > 1.6       ? 'c-red'   : ($k['ism'] > 0.8        ? 'c-amber' : 'c-green');
    $recClass   = ($k['recovery_rate'] ?? 0) > 10 ? 'c-green' : (($k['recovery_rate'] ?? 0) > 5 ? 'c-amber' : 'c-red');
    $concClass  = $k['conc_top5'] > 60  ? 'c-red'   : ($k['conc_top5'] > 40   ? 'c-amber' : 'c-green');
    $ovDocClass = $k['overdue_doc_rate'] > 50 ? 'c-red' : ($k['overdue_doc_rate'] > 30 ? 'c-amber' : 'c-green');
    $depClass   = ($k['top_client']['rate'] ?? 0) > 50 ? 'c-red' : (($k['top_client']['rate'] ?? 0) > 30 ? 'c-amber' : 'c-green');
    $ovClass    = ($k['overdue_rate'] ?? 0) > 50 ? 'c-red' : (($k['overdue_rate'] ?? 0) > 30 ? 'c-amber' : 'c-green');
@endphp

<div class="mcm-modern-page sd-page space-y-4"
     x-data="sdDashboard(@js($this->charts))">

    {{-- ═══ HEADER PBI ════════════════════════════════════════ --}}
    <div class="sd-header">
        <div class="sd-logo">mcm<small>company sas</small></div>
        <div class="sd-hdiv"></div>
        @unless($k['portfolio_empty'])
        <div class="sd-hkpi">
            <span class="sd-hkpi-val">{{ number_format($k['portfolio_total'] / 1000000, 2, '.', ',') }} mill.</span>
            <span class="sd-hkpi-label">Cartera Total</span>
        </div>
        <div class="sd-hdiv"></div>
        <div class="sd-hkpi">
            <span class="sd-hkpi-val">{{ $k['rotation'] !== null ? number_format($k['rotation'], 1, ',', '.') : '—' }}</span>
            <span class="sd-hkpi-label">Días Cartera</span>
        </div>
        <div class="sd-hdiv"></div>
        <div class="sd-hkpi" x-data="{ score: {{ $k['score'] }}, color: '{{ $k['score_color'] }}' }">
            <span class="sd-hkpi-val" :style="'color:' + ({'green':'var(--mcm-green)','amber':'var(--mcm-amber)','red':'var(--mcm-red)'}[color] ?? 'var(--mcm-muted)')">
                {{ $k['score'] }}<span style="font-size:.6rem;font-weight:500;color:var(--mcm-muted)">/100</span>
            </span>
            <span class="sd-hkpi-label">Score Salud</span>
        </div>
        @endunless
        <div class="sd-period-wrap">
            <label>Período</label>
            <span style="font-size:.8rem;font-weight:700;color:var(--mcm-text);">
                @if(count($this->selectedPeriods) === 1)
                    {{ $this->selectedPeriods[0] }}
                @elseif(count($this->selectedPeriods) > 1)
                    {{ count($this->selectedPeriods) }} períodos
                @else
                    Todos
                @endif
            </span>
        </div>
        <button wire:click="applyFilters" class="sd-haction" title="Actualizar" wire:loading.attr="disabled">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
        </button>
        <a href="{{ route('admin.exports.portfolio', ['period' => $this->selectedPeriods[0] ?? '']) }}" target="_blank" class="sd-haction" title="Exportar XLSX">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            <span>XLSX</span>
        </a>
    </div>

    {{-- ═══ FILTER BAR ═════════════════════════════════════════ --}}
    <div class="sd-filter-bar">
        <div class="sd-filter-col" style="max-width:8rem;">
            <label>Desde</label>
            <input type="date" wire:model="dateFrom" wire:change="applyFilters" class="sd-filter-input">
        </div>
        <div class="sd-filter-col" style="max-width:8rem;">
            <label>Hasta</label>
            <input type="date" wire:model="dateTo" wire:change="applyFilters" class="sd-filter-input">
        </div>
        <div class="sd-filter-actions">
            <button wire:click="resetFilters" class="sd-haction" style="border-radius:7px;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:13px;height:13px"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                Limpiar
            </button>
            <button class="sd-compare-btn {{ $compareMode ? 'active' : '' }}"
                    wire:click="toggleCompare">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:13px;height:13px;display:inline;vertical-align:-2px;margin-right:3px"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                Comparar
            </button>
        </div>
    </div>

    {{-- ═══ PANEL COMPARACIÓN ══════════════════════════════════ --}}
    @if($compareMode)
    <div class="sd-compare-panel">
        <div class="sd-compare-panel-title">Configuración de Comparación</div>

        <div class="sd-compare-type-tabs">
            <button wire:click="$set('compareType','period')" class="sd-compare-type-tab {{ $compareType === 'period' ? 'active' : '' }}">Período vs Período</button>
            <button wire:click="$set('compareType','uen')" class="sd-compare-type-tab {{ $compareType === 'uen' ? 'active' : '' }}">UEN vs UEN</button>
            <button wire:click="$set('compareType','regional')" class="sd-compare-type-tab {{ $compareType === 'regional' ? 'active' : '' }}">Regional vs Regional</button>
            <label style="display:flex;align-items:center;gap:.4rem;font-size:.78rem;color:var(--mcm-muted);cursor:pointer;margin-left:auto;">
                <input type="checkbox" wire:model.live="comparePrevious" style="accent-color:var(--mcm-accent);">
                Período anterior automático
            </label>
        </div>

        @unless($comparePrevious)
        <div class="sd-compare-grid">
            @if($compareType === 'period')
            <div class="sd-filter-col">
                <label>Período A</label>
                <select wire:model="comparePeriodA" class="sd-filter-input">
                    <option value="">Seleccionar</option>
                    @foreach($this->periodOptionsShort as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
                </select>
            </div>
            <div class="sd-filter-col">
                <label>Período B</label>
                <select wire:model="comparePeriodB" class="sd-filter-input">
                    <option value="">Seleccionar</option>
                    @foreach($this->periodOptionsShort as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
                </select>
            </div>
            @elseif($compareType === 'uen')
            <div class="sd-filter-col">
                <label>UEN A</label>
                <select wire:model="compareValueA" class="sd-filter-input">
                    <option value="">Seleccionar</option>
                    @foreach($this->uenOptions as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
                </select>
            </div>
            <div class="sd-filter-col">
                <label>UEN B</label>
                <select wire:model="compareValueB" class="sd-filter-input">
                    <option value="">Seleccionar</option>
                    @foreach($this->uenOptions as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
                </select>
            </div>
            @elseif($compareType === 'regional')
            <div class="sd-filter-col">
                <label>Regional A</label>
                <select wire:model="compareValueA" class="sd-filter-input">
                    <option value="">Seleccionar</option>
                    @foreach($this->regionalOptions as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
                </select>
            </div>
            <div class="sd-filter-col">
                <label>Regional B</label>
                <select wire:model="compareValueB" class="sd-filter-input">
                    <option value="">Seleccionar</option>
                    @foreach($this->regionalOptions as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
                </select>
            </div>
            @endif
            <div style="display:flex;align-items:flex-end;">
                <button wire:click="applyFilters" class="btn-primary" style="width:100%;">Aplicar comparación</button>
            </div>
        </div>
        @endunless
    </div>
    @endif

    {{-- ═══ EMPTY STATE ════════════════════════════════════════ --}}
    @if($k['portfolio_empty'])
    <div class="sd-chart-card">
        <div class="sd-empty">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.012 1.244l.256.512a2.25 2.25 0 0 0 2.013 1.244h3.218a2.25 2.25 0 0 0 2.013-1.244l.256-.512a2.25 2.25 0 0 1 2.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 0 0-2.15-1.588H6.911a2.25 2.25 0 0 0-2.15 1.588L2.1 13.177a2.25 2.25 0 0 0-.1.661Z" /></svg>
            <p style="font-size:.88rem;font-weight:600;margin-bottom:.25rem;">Sin datos para el período seleccionado</p>
            <p style="font-size:.78rem;">Ajusta los filtros o importa documentos de cartera.</p>
        </div>
    </div>
    @else

    {{-- ═══ KPI STRIP — 12 KPIs alineados con mcmdef ════════════ --}}
    <div class="sd-kpi-strip">

        {{-- 1. Cartera Total --}}
        <div class="sd-kpi">
            <div class="sd-kpi-label">Cartera Total</div>
            <div class="sd-kpi-value">${{ number_format($k['portfolio_total'], 0, ',', '.') }}</div>
            <div class="sd-kpi-sub">{{ number_format($k['total_docs']) }} documentos</div>
        </div>

        {{-- 2. % Cartera Crítica (>90d) --}}
        <div class="sd-kpi">
            <div class="sd-kpi-label">% Cartera Crítica (&gt;90d)</div>
            <div class="sd-kpi-value {{ $critClass }}">{{ number_format($k['critical_rate'], 1, ',', '.') }}%</div>
            <div class="sd-kpi-sub">${{ number_format($k['critical_amount'], 0, ',', '.') }}</div>
        </div>

        {{-- 3. Índice Severidad Mora (ratio, no %) --}}
        <div class="sd-kpi">
            <div class="sd-kpi-label">Índice Severidad Mora</div>
            <div class="sd-kpi-value {{ $ismClass }}">{{ number_format($k['ism'], 2, ',', '.') }}</div>
            <div class="sd-kpi-sub">Buckets 31-360+d ponderados</div>
        </div>

        {{-- 4. Rotación de Cartera --}}
        <div class="sd-kpi">
            <div class="sd-kpi-label">Rotación de Cartera</div>
            @if($k['rotation'] !== null)
                <div class="sd-kpi-value">{{ number_format($k['rotation'], 1, ',', '.') }}d</div>
                <div class="sd-kpi-sub">Días promedio de recuperación</div>
            @else
                <div class="sd-kpi-value" style="font-size:.78rem;color:var(--mcm-muted);">Sin recaudo</div>
                <div class="sd-kpi-sub">Sin recaudo en período</div>
            @endif
        </div>

        {{-- 5. % Concentración Top 5 --}}
        <div class="sd-kpi">
            <div class="sd-kpi-label">% Concentración Top 5</div>
            <div class="sd-kpi-value {{ $concClass }}">{{ number_format($k['conc_top5'], 1, ',', '.') }}%</div>
            <div class="sd-kpi-sub">Participación 5 clientes mayores</div>
        </div>

        {{-- 6. % Dependencia Cliente Mayor --}}
        <div class="sd-kpi">
            <div class="sd-kpi-label">% Dependencia Cliente Mayor</div>
            <div class="sd-kpi-value {{ $depClass }}">{{ number_format($k['top_client']['rate'], 1, ',', '.') }}%</div>
            <div class="sd-kpi-sub">{{ $k['top_client']['name'] ?? '—' }}</div>
        </div>

        {{-- 7. % Documentos Vencidos --}}
        <div class="sd-kpi">
            <div class="sd-kpi-label">% Documentos Vencidos</div>
            <div class="sd-kpi-value {{ $ovDocClass }}">{{ number_format($k['overdue_doc_rate'], 1, ',', '.') }}%</div>
            <div class="sd-kpi-sub">{{ number_format($k['overdue_docs']) }} / {{ number_format($k['total_docs']) }} docs</div>
        </div>

        {{-- 8. Recaudo del Período --}}
        <div class="sd-kpi">
            <div class="sd-kpi-label">Recaudo del Período</div>
            @if($k['recaudo_period'] > 0)
                <div class="sd-kpi-value">${{ number_format($k['recaudo_period'], 0, ',', '.') }}</div>
                <div class="sd-kpi-sub">Recaudo registrado</div>
            @else
                <div class="sd-kpi-value" style="font-size:.78rem;color:var(--mcm-muted);">Sin datos</div>
                <div class="sd-kpi-sub">Sin datos del período</div>
            @endif
        </div>

        {{-- 9. % Recuperación del Período --}}
        <div class="sd-kpi">
            <div class="sd-kpi-label">% Recuperación</div>
            @if($k['recovery_rate'] !== null)
                <div class="sd-kpi-value {{ $recClass }}">{{ number_format($k['recovery_rate'], 1, ',', '.') }}%</div>
                <div class="sd-kpi-sub">Recaudo / Cartera total</div>
            @else
                <div class="sd-kpi-value" style="font-size:.78rem;color:var(--mcm-muted);">Sin datos</div>
                <div class="sd-kpi-sub">Sin datos del período</div>
            @endif
        </div>

        {{-- 10. Presupuesto de Recaudo --}}
        <div class="sd-kpi">
            <div class="sd-kpi-label">Presupuesto Recaudo</div>
            @if($k['budget'] !== null)
                <div class="sd-kpi-value">${{ number_format($k['budget'], 0, ',', '.') }}</div>
                <div class="sd-kpi-sub">Meta configurada</div>
            @else
                <div class="sd-kpi-value" style="font-size:.78rem;color:var(--mcm-muted);">Sin datos</div>
                <div class="sd-kpi-sub">Sin datos del período</div>
            @endif
        </div>

        {{-- 11. Recaudo vs Meta --}}
        <div class="sd-kpi">
            <div class="sd-kpi-label">Recaudo vs Meta</div>
            @if($k['vs_meta_rate'] !== null)
                <div class="sd-kpi-value {{ $k['vs_meta_rate'] >= 100 ? 'c-green' : ($k['vs_meta_rate'] >= 70 ? 'c-amber' : 'c-red') }}">
                    {{ number_format($k['vs_meta_rate'], 1, ',', '.') }}%
                </div>
                <div class="sd-kpi-sub">Cumplimiento de presupuesto</div>
            @else
                <div class="sd-kpi-value" style="font-size:.78rem;color:var(--mcm-muted);">Sin datos</div>
                <div class="sd-kpi-sub">Sin datos del período</div>
            @endif
        </div>

        {{-- 12. % Saldo Negativo --}}
        <div class="sd-kpi">
            <div class="sd-kpi-label">% Saldo Negativo</div>
            @if($k['negative_rate'] !== null)
                <div class="sd-kpi-value c-amber">{{ number_format($k['negative_rate'], 1, ',', '.') }}%</div>
                <div class="sd-kpi-sub">Créditos / anticipos</div>
            @else
                <div class="sd-kpi-value" style="font-size:.78rem;color:var(--mcm-muted);">Sin datos</div>
                <div class="sd-kpi-sub">Sin saldos negativos</div>
            @endif
        </div>

    </div>

    {{-- ═══ RESULTADO COMPARACIÓN ══════════════════════════════ --}}
    @if($compareMode && $this->comparison !== null)
    <div>
        <p class="sd-section-title">Comparación de Escenarios</p>
    </div>
    <div class="sd-compare-kpis">
        @foreach(['a', 'b'] as $side)
        @php
            $scope  = $this->comparison[$side];
            $ck     = $scope['kpis'];
            $deltas = $this->comparison['deltas'] ?? [];
        @endphp
        <div>
            <div class="sd-compare-side-title">{{ $scope['label'] }}</div>
            @foreach([
                ['key' => 'portfolio_total', 'label' => 'Cartera Total',  'value' => '$'.number_format($ck['portfolio_total'],0,',','.'), 'inverse' => true],
                ['key' => 'critical_rate',   'label' => '% Crítica',      'value' => $ck['critical_rate'].'%',              'inverse' => true],
                ['key' => 'ism',             'label' => 'ISM',            'value' => number_format($ck['ism'],2,',','.'),    'inverse' => true],
                ['key' => 'recaudo_period',  'label' => 'Recaudo',        'value' => '$'.number_format($ck['recaudo_period'],0,',','.'), 'inverse' => false],
                ['key' => 'recovery_rate',   'label' => 'Recuperación',   'value' => $ck['recovery_rate'] !== null ? $ck['recovery_rate'].'%' : 'N/D', 'inverse' => false],
                ['key' => 'rotation',        'label' => 'Rotación',       'value' => $ck['rotation'] !== null ? $ck['rotation'].'d' : 'N/D', 'inverse' => true],
                ['key' => 'score',           'label' => 'Score Salud',    'value' => $ck['score'].'/100', 'inverse' => false],
            ] as $row)
            <div class="sd-compare-row">
                <span class="sd-compare-row-label">{{ $row['label'] }}</span>
                <span class="sd-compare-row-val">
                    {{ $row['value'] }}
                    @if($side === 'a' && isset($deltas[$row['key']]))
                    @php $d = $deltas[$row['key']]; @endphp
                    @if($d['direction'] === 'up')
                        <span class="{{ $row['inverse'] ? 'delta-down' : 'delta-up' }}">▲ {{ $d['value'] }}%</span>
                    @elseif($d['direction'] === 'down')
                        <span class="{{ $row['inverse'] ? 'delta-up' : 'delta-down' }}">▼ {{ $d['value'] }}%</span>
                    @else
                        <span class="delta-flat">—</span>
                    @endif
                    @endif
                </span>
            </div>
            @endforeach
        </div>
        @endforeach
    </div>
    @endif

    {{-- ═══ CHECKLIST FILTROS DIMENSIÓN ══════════════════════════ --}}
    <div class="sd-checklist-bar">

        {{-- Período --}}
        <div class="sd-checklist-section" style="min-width:7rem;">
            <div class="sd-checklist-title">
                Período
                @if(count($this->selectedPeriods) > 0)
                    <span style="font-size:.6rem;color:var(--mcm-accent);font-weight:700;margin-left:.3rem;">({{ count($this->selectedPeriods) }})</span>
                @endif
            </div>
            <div class="sd-checklist-items">
                @foreach($this->periodOptionsShort as $ym => $lbl)
                <label class="sd-check-item">
                    <input type="checkbox"
                           {{ in_array($ym, $this->selectedPeriods) ? 'checked' : '' }}
                           wire:click="togglePeriod('{{ $ym }}')">
                    {{ $lbl }}
                </label>
                @endforeach
            </div>
        </div>

        <div style="width:1px;background:var(--mcm-border);align-self:stretch;flex-shrink:0;"></div>

        {{-- Asesor --}}
        <div class="sd-checklist-section">
            <div class="sd-checklist-title">
                Asesor
                @if(count($this->selectedAdvisors) > 0)
                    <span style="font-size:.6rem;color:var(--mcm-accent);font-weight:700;margin-left:.3rem;">({{ count($this->selectedAdvisors) }})</span>
                @endif
            </div>
            <div class="sd-checklist-items">
                @foreach($this->advisorOptions as $id => $name)
                <label class="sd-check-item">
                    <input type="checkbox"
                           {{ in_array($id, $this->selectedAdvisors) ? 'checked' : '' }}
                           wire:click="toggleAdvisor('{{ $id }}')">
                    {{ $name }}
                </label>
                @endforeach
            </div>
        </div>

        <div style="width:1px;background:var(--mcm-border);align-self:stretch;flex-shrink:0;"></div>

        {{-- UEN --}}
        <div class="sd-checklist-section">
            <div class="sd-checklist-title">
                UEN
                @if(count($this->selectedUens) > 0)
                    <span style="font-size:.6rem;color:var(--mcm-accent);font-weight:700;margin-left:.3rem;">({{ count($this->selectedUens) }})</span>
                @endif
            </div>
            <div class="sd-checklist-items">
                @foreach($this->uenOptions as $val => $lbl)
                <label class="sd-check-item">
                    <input type="checkbox"
                           {{ in_array($val, $this->selectedUens) ? 'checked' : '' }}
                           wire:click="toggleUen('{{ $val }}')">
                    {{ $lbl }}
                </label>
                @endforeach
            </div>
        </div>

        <div style="width:1px;background:var(--mcm-border);align-self:stretch;flex-shrink:0;"></div>

        {{-- Canal --}}
        <div class="sd-checklist-section">
            <div class="sd-checklist-title">
                Canal
                @if(count($this->selectedChannels) > 0)
                    <span style="font-size:.6rem;color:var(--mcm-accent);font-weight:700;margin-left:.3rem;">({{ count($this->selectedChannels) }})</span>
                @endif
            </div>
            <div class="sd-checklist-items">
                @foreach($this->channelOptions as $ch => $label)
                <label class="sd-check-item">
                    <input type="checkbox"
                           {{ in_array($ch, $this->selectedChannels) ? 'checked' : '' }}
                           wire:click="toggleChannel('{{ $ch }}')">
                    {{ $label }}
                </label>
                @endforeach
            </div>
        </div>

        <div style="width:1px;background:var(--mcm-border);align-self:stretch;flex-shrink:0;"></div>

        {{-- Regional --}}
        <div class="sd-checklist-section">
            <div class="sd-checklist-title">
                Regional
                @if(count($this->selectedRegionals) > 0)
                    <span style="font-size:.6rem;color:var(--mcm-accent);font-weight:700;margin-left:.3rem;">({{ count($this->selectedRegionals) }})</span>
                @endif
            </div>
            <div class="sd-checklist-items">
                @foreach($this->regionalOptions as $val => $lbl)
                <label class="sd-check-item">
                    <input type="checkbox"
                           {{ in_array($val, $this->selectedRegionals) ? 'checked' : '' }}
                           wire:click="toggleRegional('{{ $val }}')">
                    {{ $lbl }}
                </label>
                @endforeach
            </div>
        </div>

        <div style="width:1px;background:var(--mcm-border);align-self:stretch;flex-shrink:0;"></div>

        {{-- Cliente --}}
        <div class="sd-checklist-section" style="flex:1;min-width:12rem;">
            <div class="sd-checklist-title">Cliente</div>
            <input type="text" wire:model.live.debounce.300ms="clientSearch" class="sd-filter-search" placeholder="Buscar cliente...">
            <div class="sd-checklist-items">
                <label class="sd-check-item">
                    <input type="checkbox" {{ !$this->clientId ? 'checked' : '' }} wire:click="setClient('')" style="pointer-events:{{ !$this->clientId ? 'none' : 'auto' }};accent-color:var(--mcm-accent)">
                    Todos
                </label>
                @foreach($this->clientOptions as $id => $name)
                <label class="sd-check-item">
                    <input type="checkbox" {{ (string)$this->clientId === (string)$id ? 'checked' : '' }} wire:click="setClient('{{ (string)$this->clientId === (string)$id ? '' : $id }}')">
                    {{ $name }}
                </label>
                @endforeach
            </div>
        </div>

        <div style="width:1px;background:var(--mcm-border);align-self:stretch;flex-shrink:0;"></div>

        {{-- Score drivers --}}
        <div class="sd-checklist-section">
            <div class="sd-checklist-title">Salud · {{ $k['score'] }}/100 — {{ $k['score_label'] }}</div>
            <div style="display:flex;flex-direction:column;gap:.25rem;">
                @foreach($k['score_drivers'] as $d)
                <div style="font-size:.73rem;color:var(--mcm-muted);display:flex;gap:.3rem;">
                    <span>{{ $d['icon'] }}</span>
                    <span>{{ $d['text'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ═══ GRÁFICOS ═══════════════════════════════════════════ --}}
    <div>
        <p class="sd-section-title">Evolución y Distribución</p>
    </div>

    <div class="sd-charts-grid" wire:ignore>
        <div class="sd-chart-card">
            <div class="sd-chart-title">Distribución por Antigüedad (Aging)</div>
            <div class="sd-chart-canvas"><canvas x-ref="aging"></canvas></div>
        </div>
        <div class="sd-chart-card">
            <div class="sd-chart-title">Tendencia de Exposición Crítica</div>
            <div class="sd-chart-canvas"><canvas x-ref="trend"></canvas></div>
        </div>
        <div class="sd-chart-card">
            <div class="sd-chart-title">Cartera Vencida por UEN</div>
            <div class="sd-chart-canvas"><canvas x-ref="byUen"></canvas></div>
        </div>
        <div class="sd-chart-card">
            <div class="sd-chart-title">Cartera Vencida por Canal</div>
            <div class="sd-chart-canvas"><canvas x-ref="byChannel"></canvas></div>
        </div>
        <div class="sd-chart-card full">
            <div class="sd-chart-title">Cartera Vencida por Asesor (Top 12)</div>
            <div class="sd-chart-canvas tall"><canvas x-ref="byAdvisor"></canvas></div>
        </div>
        <div class="sd-chart-card full">
            <div class="sd-chart-title">Pareto de Clientes — Top 10</div>
            <div class="sd-chart-canvas tall"><canvas x-ref="pareto"></canvas></div>
        </div>
    </div>

    @endif {{-- end portfolio_empty --}}

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('alpine:init', () => {
    if (window.__sdDashboardRegistered) return;
    window.__sdDashboardRegistered = true;

    Alpine.data('sdDashboard', (initialCharts) => ({
        charts: initialCharts,
        _instances: {},
        _themeObserver: null,

        init() {
            const self = this;
            this.$nextTick(() => { if (self.charts) self.buildAll(self.charts); });

            Livewire.on('charts-updated', (params) => {
                const data = params?.charts ?? (Array.isArray(params) ? params[0]?.charts : null);
                if (data) { self.charts = data; self.buildAll(data); }
            });

            this._themeObserver = new MutationObserver(() => {
                if (self.charts) self.buildAll(self.charts);
            });
            this._themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
        },

        destroy() {
            if (this._themeObserver) this._themeObserver.disconnect();
            Object.values(this._instances).forEach(c => { try { c.destroy(); } catch(e) {} });
            this._instances = {};
        },

        theme() {
            const dark = document.documentElement.classList.contains('dark');
            return {
                text: dark ? '#94A3B8' : '#6B7280',
                grid: dark ? 'rgba(148,163,184,.1)' : 'rgba(107,114,128,.08)',
                bg:   dark ? '#1E293B' : '#FFFFFF',
            };
        },

        colors() {
            const dark = document.documentElement.classList.contains('dark');
            return {
                blue:   dark ? '#3B82F6' : '#1E5AA8',
                navy:   dark ? '#60A5FA' : '#15427A',
                green:  dark ? '#4ADE80' : '#22C55E',
                amber:  dark ? '#FBBF24' : '#F59E0B',
                orange: dark ? '#FB923C' : '#F97316',
                red:    dark ? '#F87171' : '#EF4444',
                purple: dark ? '#A78BFA' : '#8B5CF6',
                cyan:   dark ? '#22D3EE' : '#0EA5E9',
            };
        },

        baseOptions(type, horizontal = false) {
            const t = this.theme();
            return {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                plugins: {
                    legend: {
                        display: type === 'doughnut' || type === 'line',
                        position: type === 'doughnut' ? 'right' : 'bottom',
                        labels: { boxWidth: 10, color: t.text, font: { size: 10 }, padding: 8 }
                    },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: type === 'doughnut' ? {} : {
                    x: horizontal
                        ? { grid: { color: t.grid }, ticks: { color: t.text, font: { size: 10 }, callback: v => '$'+(v/1e6).toFixed(1)+'M' } }
                        : { grid: { display: false }, ticks: { color: t.text, font: { size: 10 } } },
                    y: horizontal
                        ? { grid: { display: false }, ticks: { color: t.text, font: { size: 10 } } }
                        : { grid: { color: t.grid }, ticks: { color: t.text, font: { size: 10 }, callback: v => '$'+(v/1e6).toFixed(1)+'M' } },
                },
                indexAxis: horizontal ? 'y' : 'x',
            };
        },

        upsert(ref, type, data, extraOpts = {}, horizontal = false) {
            const canvas = this.$refs[ref];
            if (!canvas) return;
            const opts = Object.assign({}, this.baseOptions(type, horizontal), extraOpts);
            if (this._instances[ref]) {
                try { this._instances[ref].destroy(); } catch(e) {}
            }
            try {
                this._instances[ref] = new Chart(canvas, { type, data, options: opts });
            } catch(e) {
                console.warn('MCM chart error [' + ref + ']:', e);
            }
        },

        buildAll(data) {
            if (!data) return;
            const mc = this.colors();
            const t  = this.theme();

            // Aging donut
            if (data.aging?.datasets?.[0]) {
                const d = JSON.parse(JSON.stringify(data.aging));
                d.datasets[0].backgroundColor = [mc.green, mc.blue, mc.amber, mc.orange, mc.red];
                d.datasets[0].borderColor = t.bg;
                d.datasets[0].borderWidth = 2;
                this.upsert('aging', 'doughnut', d, {
                    plugins: {
                        legend: { display: true, position: 'right', labels: { boxWidth: 10, color: t.text, font: { size: 10 }, padding: 8 } },
                        tooltip: { callbacks: { label(ctx) { const p = data.pcts ? data.pcts[ctx.dataIndex]+'%' : ''; return ' $'+ctx.raw.toLocaleString('es-CO')+(p?' ('+p+')':''); } } }
                    },
                    cutout: '60%',
                });
            }

            // Trend line
            if (data.trend?.datasets) {
                const d = JSON.parse(JSON.stringify(data.trend));
                if (d.datasets[0]) { d.datasets[0].borderColor = mc.blue; d.datasets[0].backgroundColor = 'rgba(30,90,168,.07)'; d.datasets[0].tension = 0.3; d.datasets[0].fill = true; }
                if (d.datasets[1]) { d.datasets[1].borderColor = mc.red;  d.datasets[1].backgroundColor = 'rgba(239,68,68,.05)'; d.datasets[1].tension = 0.3; d.datasets[1].fill = false; }
                this.upsert('trend', 'line', d, {
                    scales: {
                        x: { grid: { display: false }, ticks: { color: t.text, font: { size: 9 } } },
                        y:  { grid: { color: t.grid }, ticks: { color: t.text, font: { size: 10 }, callback: v => '$'+(v/1e6).toFixed(1)+'M' } },
                        y1: { position: 'right', grid: { display: false }, ticks: { color: t.text, font: { size: 10 }, callback: v => v+'%' }, min: 0, max: 100 },
                    },
                    plugins: { legend: { display: true, position: 'bottom', labels: { boxWidth: 10, color: t.text, font: { size: 10 } } } }
                });
            }

            // By UEN
            if (data.by_uen?.datasets?.[0]) {
                const d = JSON.parse(JSON.stringify(data.by_uen));
                d.datasets[0].backgroundColor = mc.blue;
                this.upsert('byUen', 'bar', d, {
                    plugins: { legend: { display: false }, tooltip: { callbacks: { label(ctx) { return ' $'+ctx.parsed.y.toLocaleString('es-CO'); } } } }
                });
            }

            // By Channel
            if (data.by_channel?.datasets?.[0]) {
                const d = JSON.parse(JSON.stringify(data.by_channel));
                d.datasets[0].backgroundColor = mc.cyan;
                this.upsert('byChannel', 'bar', d, {
                    plugins: { legend: { display: false }, tooltip: { callbacks: { label(ctx) { return ' $'+ctx.parsed.y.toLocaleString('es-CO'); } } } }
                });
            }

            // By Advisor (horizontal)
            if (data.by_advisor?.datasets?.[0]) {
                const d = JSON.parse(JSON.stringify(data.by_advisor));
                d.datasets[0].backgroundColor = mc.purple;
                this.upsert('byAdvisor', 'bar', d, {
                    indexAxis: 'y',
                    plugins: { legend: { display: false }, tooltip: { callbacks: { label(ctx) { return ' $'+ctx.parsed.x.toLocaleString('es-CO'); } } } },
                    scales: {
                        x: { grid: { color: t.grid }, ticks: { color: t.text, font: { size: 10 }, callback: v => '$'+(v/1e6).toFixed(1)+'M' } },
                        y: { grid: { display: false }, ticks: { color: t.text, font: { size: 10 } } },
                    },
                }, true);
            }

            // Pareto
            if (data.pareto?.datasets?.[0]) {
                const d = JSON.parse(JSON.stringify(data.pareto));
                d.datasets[0].backgroundColor = mc.blue; d.datasets[0].borderColor = mc.navy;
                if (d.datasets[1]) { d.datasets[1].borderColor = mc.amber; d.datasets[1].pointBackgroundColor = mc.amber; d.datasets[1].backgroundColor = 'rgba(245,158,11,.07)'; d.datasets[1].tension = 0.3; }
                try {
                    if (this._instances['pareto']) { try { this._instances['pareto'].destroy(); } catch(e) {} }
                    this._instances['pareto'] = new Chart(this.$refs['pareto'], {
                        data: d,
                        options: {
                            responsive: true, maintainAspectRatio: false, animation: false,
                            plugins: { legend: { display: true, position: 'bottom', labels: { boxWidth: 10, color: t.text, font: { size: 10 } } } },
                            scales: {
                                x:  { grid: { display: false }, ticks: { color: t.text, font: { size: 9 }, maxRotation: 25 } },
                                y:  { position: 'left',  grid: { color: t.grid }, ticks: { color: t.text, callback: v => '$'+(v/1e6).toFixed(1)+'M', font: { size: 10 } } },
                                y1: { position: 'right', grid: { display: false }, ticks: { color: t.text, callback: v => v+'%', font: { size: 10 } }, min: 0, max: 100 },
                            },
                        },
                    });
                } catch(e) { console.warn('MCM pareto error:', e); }
            }
        },
    }));
});
</script>
@endpush

</x-filament-panels::page>
