<x-filament-panels::page>

@include('filament.pages.partials.modern-dashboard-styles')

@push('styles')
<style>
/* Dashboard — fondo #2852a0 (azul imagen) + tarjetas #D1DCE5 */
.sd-page.mcm-modern-page {
    --mcm-app-bg: #2852a0;
    --mcm-primary: #2852a0;
    --mcm-primary-hover: #1f4285;
    --mcm-accent: #2852a0;
    --mcm-accent-strong: #1f4285;
    --mcm-accent-soft: rgba(40, 82, 160, 0.12);
    --mcm-blue: #2852a0;
    --mcm-blue-bg: #e4ebf2;
    --mcm-bg: transparent;
    --mcm-surface: #d1dce5;
    --mcm-surface-soft: #e4ebf2;
    --mcm-border: #b8c8d6;
    --mcm-text: #1a202c;
    --mcm-text-strong: #0f172a;
    --mcm-muted: #475569;
    --mcm-input-surface: #ffffff;
    --mcm-input-text: #1a202c;
    --sd-bar-dark: #1f4285;
    --sd-bar-mid: #2852a0;
}
body:has(.sd-page),
body:has(.sd-page) .fi-main,
body:has(.sd-page) .fi-page,
body:has(.sd-page) .fi-page-content {
    background: #2852a0 !important;
    background-image: linear-gradient(165deg, #2852a0 0%, #3268b5 50%, #2852a0 100%) !important;
}

/* ── Variables de página ─────────────────────────────── */
.sd-page { --sd-header-h: 3.25rem; overflow-x: hidden; max-width: 100%; }
.sd-page .sd-filters-grid--dashboard { margin-bottom: .25rem; }

/* ── Header PBI (barra oscura, texto claro) ─────────── */
.sd-page .sd-header {
    display: flex;
    align-items: center;
    gap: 0;
    background: linear-gradient(135deg, var(--sd-bar-dark) 0%, var(--sd-bar-mid) 55%, #3268b5 100%);
    border: 1px solid rgba(0, 0, 0, 0.25);
    border-radius: 12px;
    padding: 0 1rem;
    min-height: var(--sd-header-h);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.22);
    overflow: hidden;
    color: #fff;
}
.sd-page .sd-logo {
    display: flex;
    align-items: center;
    padding-right: 1rem;
    flex-shrink: 0;
    line-height: 1;
}
.sd-page .sd-logo .mcm-brand-logo,
.sd-page .sd-logo img {
    display: block;
    height: 2.1rem;
    width: auto;
    max-width: 8.5rem;
    object-fit: contain;
}
.sd-page .sd-hdiv {
    width: 1px;
    background: rgba(255, 255, 255, 0.22);
    align-self: stretch;
    margin: .5rem .75rem;
}
.sd-page .sd-hkpi {
    display: flex;
    flex-direction: column;
    padding: .15rem .75rem;
    flex-shrink: 0;
}
.sd-page .sd-hkpi-val {
    font-size: 1.1rem;
    font-weight: 800;
    color: #fff;
    line-height: 1;
}
.sd-page .sd-hkpi-label {
    font-size: .62rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: rgba(255, 255, 255, 0.72);
}
.sd-page .sd-period-wrap {
    display: flex;
    align-items: center;
    gap: .4rem;
    margin-left: auto;
    flex-shrink: 0;
    flex-wrap: wrap;
}
.sd-page .sd-period-wrap label,
.sd-page .sd-period-wrap .sd-period-value,
.sd-page .sd-period-wrap .sd-period-hint {
    color: rgba(255, 255, 255, 0.92);
}
.sd-page .sd-period-wrap label {
    font-size: .7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.sd-page .sd-period-select {
    padding: .3rem .6rem;
    border-radius: 6px;
    border: 1px solid rgba(255, 255, 255, 0.35);
    background: rgba(255, 255, 255, 0.12);
    color: #fff;
    font-size: .8rem;
    font-weight: 600;
    cursor: pointer;
    outline: none;
}
.sd-page .sd-period-select:focus {
    border-color: #fff;
    box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.25);
}
.sd-page .sd-haction {
    display: flex;
    align-items: center;
    gap: .35rem;
    padding: .3rem .5rem;
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.35);
    color: #fff;
    cursor: pointer;
    font-size: .75rem;
    font-weight: 600;
    text-decoration: none;
    margin-left: .4rem;
    flex-shrink: 0;
}
.sd-page .sd-haction:hover {
    background: rgba(255, 255, 255, 0.18);
    border-color: #fff;
    color: #fff;
}
.sd-page .sd-haction svg { width: 14px; height: 14px; }

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
    color: var(--mcm-text);
}
.sd-filter-col { display: flex; flex-direction: column; gap: .2rem; min-width: 8rem; flex: 1; }
.sd-filter-col.sd-filter-col--date { min-width: 9rem; max-width: 11rem; flex: 0 1 auto; }
.sd-filter-col label { font-size: .67rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--mcm-muted); }
.sd-filter-input {
    padding: .35rem .6rem; border-radius: 7px;
    border: 1px solid #b8c8d6; background: var(--mcm-input-surface, #d1dce5);
    color: var(--mcm-input-text, #1a202c); font-size: .8rem; outline: none;
    appearance: none; -webkit-appearance: none;
}
.sd-filter-input:focus { border-color: var(--mcm-accent); box-shadow: 0 0 0 2px color-mix(in srgb,var(--mcm-accent) 15%,transparent); }
.sd-filter-actions {
    display: flex;
    align-items: center;
    gap: .5rem;
    flex-shrink: 0;
    margin-left: auto;
}
.sd-filter-bar .sd-compare-btn {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    white-space: nowrap;
}

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
.sd-kpi-value { font-size: 1.1rem; font-weight: 800; color: var(--mcm-text-strong); line-height: 1; }
.sd-kpi-sub { font-size: .67rem; color: var(--mcm-muted); margin-top: .2rem; }
.sd-kpi-value.c-green  { color: var(--mcm-green) !important; }
.sd-kpi-value.c-amber  { color: var(--mcm-amber) !important; }
.sd-kpi-value.c-red    { color: var(--mcm-red) !important; }
.sd-kpi-value.c-blue   { color: var(--mcm-brand) !important; }
.sd-kpi-sub .c-green,
.sd-kpi-sub .c-amber,
.sd-kpi-sub .c-red { font-weight: 700; }
.sd-kpi-sub .c-green { color: var(--mcm-green) !important; }
.sd-kpi-sub .c-amber { color: var(--mcm-amber) !important; }
.sd-kpi-sub .c-red   { color: var(--mcm-red) !important; }

/* ── Score ring ──────────────────────────────────────── */
.sd-score-kpi {
    background: var(--mcm-surface); border: 1px solid var(--mcm-border);
    border-radius: 10px; padding: .7rem .85rem;
    box-shadow: var(--mcm-shadow-soft);
    display: flex; align-items: center; gap: .6rem;
}
.sd-score-kpi svg { flex-shrink: 0; }

.sd-checklist-section { min-width: 0; }
.sd-filter-search:focus { border-color: var(--mcm-accent); }
.sd-client-filter-hint {
    font-size: .68rem;
    color: var(--mcm-muted);
    line-height: 1.38;
    padding: .15rem 0 .2rem;
    margin: 0 0 .15rem;
}
.sd-client-clear {
    display: inline;
    font: inherit;
    font-size: .72rem;
    font-weight: 600;
    color: var(--mcm-accent);
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    margin-left: .25rem;
    text-decoration: underline;
}
.sd-cascade-hint { display: none !important; }
.sd-chart-hint { display: none !important; }
.sd-client-filter-hint { display: none !important; }
.sd-daterange-block {
    display: flex;
    flex-direction: column;
    gap: .35rem;
    min-width: 11rem;
    max-width: 14rem;
}
.sd-daterange-block label.sd-daterange-title {
    font-size: .65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: var(--mcm-muted);
}
.sd-daterange-row {
    display: flex;
    gap: .45rem;
    align-items: flex-end;
    flex-wrap: wrap;
}
.sd-daterange-row .sd-filter-col { min-width: 0; flex: 1; }

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
.sd-chart-hint { font-size: .65rem; color: var(--mcm-muted); margin: -.35rem 0 .5rem; font-weight: 500; }
.sd-chart-canvas { height: 15rem; position: relative; cursor: crosshair; }
.sd-chart-canvas.tall { height: 18rem; }

/* ── Comparison KPI table ────────────────────────────── */
.sd-compare-kpis {
    display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;
    background: var(--mcm-surface); border: 1px solid var(--mcm-border);
    border-radius: 12px; padding: 1rem 1.25rem;
    box-shadow: var(--mcm-shadow-soft);
}
.sd-compare-side-title { font-size: .82rem; font-weight: 700; color: var(--mcm-text-strong); border-bottom: 2px solid var(--mcm-brand); padding-bottom: .4rem; margin-bottom: .6rem; }
.sd-compare-row { display: flex; justify-content: space-between; align-items: center; padding: .3rem 0; border-bottom: 1px solid var(--mcm-border); font-size: .78rem; }
.sd-compare-row:last-child { border-bottom: none; }
.sd-compare-row-label { color: var(--mcm-muted); }
.sd-compare-row-val { font-weight: 700; color: var(--mcm-text-strong); display: flex; align-items: center; gap: .4rem; }
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
    $rotClass   = ($k['rotation'] ?? 999) <= 45 ? 'c-green' : (($k['rotation'] ?? 999) <= 90 ? 'c-amber' : 'c-red');
    $recClass   = ($k['recovery_rate'] ?? 0) >= 15 ? 'c-green' : (($k['recovery_rate'] ?? 0) >= 8 ? 'c-amber' : 'c-red');
    $concClass  = $k['conc_top5'] > 60  ? 'c-red'   : ($k['conc_top5'] > 40   ? 'c-amber' : 'c-green');
    $ovDocClass = $k['overdue_doc_rate'] > 50 ? 'c-red' : ($k['overdue_doc_rate'] > 30 ? 'c-amber' : 'c-green');
    $ovClass    = ($k['overdue_rate'] ?? 0) > 50 ? 'c-red' : (($k['overdue_rate'] ?? 0) > 30 ? 'c-amber' : 'c-green');
    $depClass   = ($k['top_client']['rate'] ?? 0) > 50 ? 'c-red' : (($k['top_client']['rate'] ?? 0) > 30 ? 'c-amber' : 'c-green');
    $negClass   = ($k['negative_rate'] ?? 0) > 5 ? 'c-red' : (($k['negative_rate'] ?? 0) > 2 ? 'c-amber' : 'c-green');
    $recaudoSub = collect([
        ($k['collection_load_ref'] ?? null) ? 'Carga ' . $k['collection_load_ref'] : null,
        $k['collection_date_label'] ?? null,
    ])->filter()->implode(' · ');
@endphp

<div class="mcm-modern-page sd-page space-y-4"
     x-data="sdDashboard(@js($this->charts), @js($this->getId()))">

    {{-- ═══ HEADER PBI ════════════════════════════════════════ --}}
    <div class="sd-header">
        <div class="sd-logo">@include('filament.partials.mcm-brand-logo')</div>
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
            <label>Contabilización</label>
            <span class="sd-period-value" style="font-size:.8rem;font-weight:700;">
                {{ $this->activeDateRangeLabel }}
            </span>
            <span class="sd-period-hint" style="font-size:.7rem;margin-left:.65rem;">
                Mora al {{ $this->moraConsultationLabel }}
            </span>
        </div>
        <button wire:click="applyFilters" class="sd-haction" title="Actualizar" wire:loading.attr="disabled">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
        </button>
        <a href="{{ route('admin.exports.portfolio', ['period' => '']) }}" target="_blank" class="sd-haction" title="Exportar XLSX">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            <span>XLSX</span>
        </a>
    </div>

    {{-- ═══ FILTER BAR: rango de contabilización + acciones ═══ --}}
    <div class="sd-filter-bar">
        <div class="sd-filter-col sd-filter-col--date">
            <label>Desde (contabilización)</label>
            <input type="date" wire:model.live="dateFrom" class="sd-filter-input">
        </div>
        <div class="sd-filter-col sd-filter-col--date">
            <label>Hasta (contabilización)</label>
            <input type="date" wire:model.live="dateTo" class="sd-filter-input">
            <span class="sd-filter-hint" style="font-size:.68rem;color:var(--mcm-muted);margin-top:.2rem;display:block;">
                Si define «Hasta», los días de mora se calculan a esa fecha; si no, a hoy ({{ \Carbon\Carbon::today()->format('d/m/Y') }}).
            </span>
        </div>
        <div class="sd-filter-actions">
            <button type="button"
                    wire:click="resetFilters"
                    class="sd-compare-btn {{ $this->hasActiveFilters ? 'active' : '' }}"
                    title="Quitar todos los filtros">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:13px;height:13px;display:inline;vertical-align:-2px;margin-right:3px"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                Limpiar
            </button>
            <button type="button"
                    class="sd-compare-btn {{ $compareMode ? 'active' : '' }}"
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
                <label>Mes contabilización A</label>
                <select wire:model="comparePeriodA" class="sd-filter-input">
                    <option value="">Seleccionar</option>
                    @foreach($this->accountingMonthOptions as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
                </select>
            </div>
            <div class="sd-filter-col">
                <label>Mes contabilización B</label>
                <select wire:model="comparePeriodB" class="sd-filter-input">
                    <option value="">Seleccionar</option>
                    @foreach($this->accountingMonthOptions as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
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
                <div class="sd-kpi-value {{ $rotClass }}">{{ number_format($k['rotation'], 1, ',', '.') }}d</div>
                <div class="sd-kpi-sub">Días promedio de recuperación</div>
            @else
                <div class="sd-kpi-value" style="font-size:.78rem;color:var(--mcm-muted);">Sin recaudo</div>
                <div class="sd-kpi-sub">Sin carga de recaudo activa</div>
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

        {{-- 7. % Documentos Vencidos (+ peso en cartera) --}}
        <div class="sd-kpi">
            <div class="sd-kpi-label">% Documentos Vencidos</div>
            <div class="sd-kpi-value {{ $ovDocClass }}">{{ number_format($k['overdue_doc_rate'], 1, ',', '.') }}%</div>
            <div class="sd-kpi-sub">{{ number_format($k['overdue_docs']) }} / {{ number_format($k['total_docs']) }} docs con días de mora</div>
            <div class="sd-kpi-sub">% cartera vencida (saldo): <span class="{{ $ovClass }}">{{ number_format($k['overdue_rate'], 1, ',', '.') }}%</span> · pesa ${{ number_format($k['overdue_amount'], 0, ',', '.') }}</div>
        </div>

        {{-- 8. Recaudo del Período --}}
        <div class="sd-kpi">
            <div class="sd-kpi-label">Recaudo</div>
            @if(($k['has_recaudo_load'] ?? false) && $k['recaudo_period'] > 0)
                <div class="sd-kpi-value c-blue">${{ number_format($k['recaudo_period'], 0, ',', '.') }}</div>
                <div class="sd-kpi-sub">{{ $recaudoSub }}</div>
            @elseif($k['has_recaudo_load'] ?? false)
                <div class="sd-kpi-value" style="font-size:.78rem;color:var(--mcm-muted);">$0</div>
                <div class="sd-kpi-sub">{{ $recaudoSub }} · sin filas en filtros</div>
            @else
                <div class="sd-kpi-value" style="font-size:.78rem;color:var(--mcm-muted);">Sin datos</div>
                <div class="sd-kpi-sub">Sin carga de recaudo activa</div>
            @endif
        </div>

        {{-- 9. % Recuperación del Período --}}
        <div class="sd-kpi">
            <div class="sd-kpi-label">% Recuperación</div>
            @if($k['recovery_rate'] !== null)
                <div class="sd-kpi-value {{ $recClass }}">{{ number_format($k['recovery_rate'], 1, ',', '.') }}%</div>
                <div class="sd-kpi-sub">Recaudo / Cartera · {{ $recaudoSub }}</div>
            @elseif($k['has_recaudo_load'] ?? false)
                <div class="sd-kpi-value" style="font-size:.78rem;color:var(--mcm-muted);">—</div>
                <div class="sd-kpi-sub">{{ $recaudoSub }} · sin monto en filtros</div>
            @else
                <div class="sd-kpi-value" style="font-size:.78rem;color:var(--mcm-muted);">Sin datos</div>
                <div class="sd-kpi-sub">Sin carga de recaudo activa</div>
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
                <div class="sd-kpi-value {{ $negClass }}">{{ number_format($k['negative_rate'], 1, ',', '.') }}%</div>
                <div class="sd-kpi-sub">Créditos / anticipos</div>
            @elseif(!empty($k['negative_by_document_type']))
                <div class="sd-kpi-value" style="font-size:.78rem;color:var(--mcm-muted);">—</div>
                <div class="sd-kpi-sub">% cartera: sin recaudo del período</div>
            @else
                <div class="sd-kpi-value" style="font-size:.78rem;color:var(--mcm-muted);">Sin datos</div>
                <div class="sd-kpi-sub">Sin saldos negativos</div>
            @endif
            @if(!empty($k['negative_by_document_type']))
                <div class="sd-kpi-sub" style="margin-top:.35rem;line-height:1.35;">
                    <span style="font-weight:700;color:var(--mcm-text);">Por tipo de documento:</span>
                    @foreach(array_slice($k['negative_by_document_type'], 0, 5) as $nb)
                        <div>{{ $nb['document_type'] }} · {{ number_format($nb['share_pct'], 1, ',', '.') }}% del saldo negativo (${{ number_format(abs($nb['amount']), 0, ',', '.') }})</div>
                    @endforeach
                </div>
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

    {{-- ═══ CHECKLIST FILTROS: UEN → Canal → Asesor → Cliente → Regional → Tipo → Período → Emisión ═══ --}}
    <div class="sd-filters-grid sd-filters-grid--dashboard">
        @php
            $sdSelUenTrim = array_map('trim', $this->selectedUens);
            $sdSelChTrim = array_map('trim', $this->selectedChannels);
            $sdSelRegTrim = array_map('trim', $this->selectedRegionals);
        @endphp

        {{-- UEN --}}
        <div class="sd-filter-card sd-checklist-section sd-filt-uen">
            <div class="sd-checklist-title">
                UEN
                @if(count($this->selectedUens) > 0)
                    <span style="font-size:.6rem;color:var(--mcm-accent);font-weight:700;margin-left:.3rem;">({{ count($this->selectedUens) }})</span>
                @endif
            </div>
            <div class="sd-checklist-items">
                @foreach($this->uenOptions as $val => $lbl)
                <label class="sd-check-item" wire:key="uen-opt-{{ md5((string) $val) }}">
                    <input type="checkbox"
                           {{ in_array(trim((string) $val), $sdSelUenTrim, true) ? 'checked' : '' }}
                           wire:click='toggleUen(@json($val))'>
                    {{ $lbl }}
                </label>
                @endforeach
            </div>
        </div>

        {{-- Canal --}}
        <div class="sd-filter-card sd-checklist-section sd-filt-canal">
            <div class="sd-checklist-title">
                Canal
                @if(count($this->selectedChannels) > 0)
                    <span style="font-size:.6rem;color:var(--mcm-accent);font-weight:700;margin-left:.3rem;">({{ count($this->selectedChannels) }})</span>
                @endif
            </div>
            <div class="sd-checklist-items">
                @foreach($this->channelOptions as $ch => $label)
                <label class="sd-check-item" wire:key="ch-opt-{{ md5((string) $ch) }}">
                    <input type="checkbox"
                           {{ in_array(trim((string) $ch), $sdSelChTrim, true) ? 'checked' : '' }}
                           wire:click='toggleChannel(@json($ch))'>
                    {{ $label }}
                </label>
                @endforeach
            </div>
        </div>

        {{-- Asesor --}}
        <div class="sd-filter-card sd-checklist-section sd-filt-asesor">
            <div class="sd-checklist-title">
                Asesor
                @if(count($this->selectedAdvisors) > 0)
                    <span style="font-size:.6rem;color:var(--mcm-accent);font-weight:700;margin-left:.3rem;">({{ count($this->selectedAdvisors) }})</span>
                @endif
            </div>
            <input type="text" wire:model.live.debounce.300ms="advisorSearch" class="sd-filter-search" placeholder="Buscar asesor...">
            <div class="sd-checklist-items">
                @foreach($this->advisorOptions as $id => $name)
                @php $advId = (int) $id; @endphp
                <label class="sd-check-item" wire:key="adv-opt-{{ $advId }}">
                    <input type="checkbox"
                           wire:key="adv-cb-{{ $advId }}"
                           {{ in_array($advId, array_map('intval', $this->selectedAdvisors), true) ? 'checked' : '' }}
                           wire:click="toggleAdvisor({{ $advId }})">
                    {{ $name }}
                </label>
                @endforeach
            </div>
        </div>

        {{-- Cliente (cascada: solo clientes con documentos activos bajo UEN/Canal/Asesor/…) --}}
        <div class="sd-filter-card sd-filter-card--wide sd-checklist-section sd-filt-cliente">
            <div class="sd-checklist-title">Cliente</div>
            <input type="text" wire:model.live.debounce.300ms="clientSearch" class="sd-filter-search" placeholder="Buscar cliente...">
            <div class="sd-checklist-items">
                @foreach($this->clientOptions as $id => $name)
                <label class="sd-check-item" wire:key="client-opt-{{ (int) $id }}">
                    <input type="checkbox" {{ (string)$this->clientId === (string)$id ? 'checked' : '' }} wire:click="setClient('{{ (string)$this->clientId === (string)$id ? '' : $id }}')">
                    {{ $name }}
                </label>
                @endforeach
            </div>
        </div>

        {{-- Tipo de documento --}}
        <div class="sd-filter-card sd-checklist-section sd-filt-tipo">
            <div class="sd-checklist-title">
                Tipo
                @if(count($this->selectedDocumentTypes) > 0)
                    <span style="font-size:.6rem;color:var(--mcm-accent);font-weight:700;margin-left:.3rem;">({{ count($this->selectedDocumentTypes) }})</span>
                @endif
            </div>
            <div class="sd-checklist-items">
                @foreach($this->documentTypeOptions as $dtVal => $dtLabel)
                <label class="sd-check-item">
                    <input type="checkbox"
                           {{ in_array($dtVal, $this->selectedDocumentTypes, true) ? 'checked' : '' }}
                           wire:click='toggleDocumentType(@json($dtVal))'>
                    {{ $dtLabel }}
                </label>
                @endforeach
            </div>
        </div>

        {{-- Regional --}}
        <div class="sd-filter-card sd-checklist-section sd-filt-regional">
            <div class="sd-checklist-title">
                Regional
                @if(count($this->selectedRegionals) > 0)
                    <span style="font-size:.6rem;color:var(--mcm-accent);font-weight:700;margin-left:.3rem;">({{ count($this->selectedRegionals) }})</span>
                @endif
            </div>
            <div class="sd-checklist-items">
                @foreach($this->regionalOptions as $val => $lbl)
                <label class="sd-check-item" wire:key="reg-opt-{{ md5((string) $val) }}">
                    <input type="checkbox"
                           {{ in_array(trim((string) $val), $sdSelRegTrim, true) ? 'checked' : '' }}
                           wire:click='toggleRegional(@json($val))'>
                    {{ $lbl }}
                </label>
                @endforeach
            </div>
        </div>

        {{-- Score drivers --}}
        <div class="sd-filter-card sd-filter-card--score sd-checklist-section sd-filt-score">
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
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
<script>
document.addEventListener('alpine:init', () => {
    if (window.__sdDashboardRegistered) return;
    window.__sdDashboardRegistered = true;

    if (typeof Chart !== 'undefined' && typeof ChartDataLabels !== 'undefined') {
        try { Chart.register(ChartDataLabels); } catch (e) { /* ya registrado */ }
    }

    Alpine.data('sdDashboard', (initialCharts, livewireId) => ({
        charts: initialCharts,
        livewireId: livewireId,
        _instances: {},
        _themeObserver: null,
        _hasDatalabels: typeof ChartDataLabels !== 'undefined',

        fmtShortMoney(v) {
            const n = Number(v);
            if (!isFinite(n)) return '';
            const a = Math.abs(n);
            if (a >= 1e9) return '$' + (n / 1e9).toFixed(1) + 'B';
            if (a >= 1e6) return '$' + (n / 1e6).toFixed(1) + 'M';
            if (a >= 1e3) return '$' + (n / 1e3).toFixed(0) + 'K';
            return '$' + Math.round(n).toLocaleString('es-CO');
        },

        chartLayoutPadding(extra = {}) {
            return {
                padding: Object.assign({ top: 30, right: 18, left: 6, bottom: 6 }, extra),
            };
        },

        moneyDatalabels(t, pcts = null) {
            const self = this;
            return {
                clip: false,
                color: t.text,
                font: { size: 9, weight: '700' },
                formatter: (v, ctx) => {
                    const money = self.fmtShortMoney(v);
                    const pct = pcts?.[ctx.dataIndex];
                    return pct != null && pct !== '' ? money + '\n' + pct + '%' : money;
                },
                anchor: 'end',
                align: 'top',
                offset: 4,
            };
        },

        /** Etiquetas fuera de la barra (eje horizontal) — legibles sobre fondo claro */
        horizontalBarMoneyLabels(t, pcts = null) {
            const dark = document.documentElement.classList.contains('dark');
            const self = this;
            return {
                clip: false,
                display: true,
                anchor: 'end',
                align: 'right',
                offset: 8,
                color: dark ? '#F8FAFC' : '#0F172A',
                backgroundColor: dark ? 'rgba(15,23,42,.88)' : 'rgba(255,255,255,.94)',
                borderColor: dark ? 'rgba(148,163,184,.35)' : 'rgba(15,23,42,.12)',
                borderRadius: 4,
                borderWidth: 1,
                padding: { top: 2, bottom: 2, left: 5, right: 5 },
                font: { size: 10, weight: '700' },
                formatter: (v, ctx) => {
                    const money = self.fmtShortMoney(v);
                    const pct = pcts?.[ctx.dataIndex];
                    return pct != null && pct !== '' ? money + ' · ' + pct + '%' : money;
                },
            };
        },

        lwCall(method, ...args) {
            try {
                const c = window.Livewire?.find(this.livewireId);
                if (c) c.call(method, ...args);
            } catch (e) { console.warn('MCM lwCall', e); }
        },

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
                text: dark ? '#94A3B8' : '#475569',
                grid: dark ? 'rgba(148,163,184,.12)' : 'rgba(71,85,105,.12)',
                bg:   dark ? '#1E293B' : '#D1DCE5',
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
                const raw = JSON.parse(JSON.stringify(data.aging));
                const bucketKeys = raw.bucket_keys || raw.risk_keys || [];
                const d = { labels: raw.labels, datasets: raw.datasets };
                if (!d.datasets[0].backgroundColor?.length) {
                    d.datasets[0].backgroundColor = ['#10b981', '#3b82f6', '#6366f1', '#f59e0b', '#f97316', '#ef4444', '#991b1b'];
                }
                d.datasets[0].borderColor = t.bg;
                d.datasets[0].borderWidth = 2;
                const self = this;
                const dlAging = self._hasDatalabels ? {
                    display: (ctx) => (Number(raw.pcts?.[ctx.dataIndex] ?? 0) >= 3),
                    color: '#fff',
                    font: { size: 11, weight: '700' },
                    textStrokeColor: 'rgba(0,0,0,0.35)',
                    textStrokeWidth: 2,
                    formatter: (_, ctx) => (raw.pcts?.[ctx.dataIndex] != null ? raw.pcts[ctx.dataIndex] + '%' : ''),
                } : false;
                this.upsert('aging', 'doughnut', d, {
                    onClick: (e, elements) => {
                        if (!elements?.length) return;
                        const idx = elements[0].index;
                        const key = bucketKeys[idx];
                        if (key) self.lwCall('filterFromChart', 'aging', key);
                    },
                    plugins: {
                        legend: { display: true, position: 'right', labels: { boxWidth: 10, color: t.text, font: { size: 10 }, padding: 8 } },
                        tooltip: { callbacks: { label(ctx) { const p = raw.pcts ? raw.pcts[ctx.dataIndex]+'%' : ''; return ' $'+ctx.raw.toLocaleString('es-CO')+(p?' ('+p+')':''); } } },
                        ...(dlAging ? { datalabels: dlAging } : {}),
                    },
                    cutout: '60%',
                });
            }

            // Trend line
            if (data.trend?.datasets) {
                const raw = JSON.parse(JSON.stringify(data.trend));
                const yms = raw.yms || [];
                const d = { labels: raw.labels, datasets: raw.datasets };
                if (d.datasets[0]) { d.datasets[0].borderColor = mc.blue; d.datasets[0].backgroundColor = 'rgba(30,90,168,.07)'; d.datasets[0].tension = 0.3; d.datasets[0].fill = true; }
                if (d.datasets[1]) { d.datasets[1].borderColor = mc.red;  d.datasets[1].backgroundColor = 'rgba(239,68,68,.05)'; d.datasets[1].tension = 0.3; d.datasets[1].fill = false; }
                const self = this;
                const dlTrend = self._hasDatalabels ? {
                    color: t.text,
                    font: { size: 8, weight: '600' },
                    align: 'top',
                    offset: 5,
                    clip: false,
                    formatter: (v, ctx) => ctx.datasetIndex === 0 ? self.fmtShortMoney(v) : (Number(v).toFixed(0) + '%'),
                } : false;
                this.upsert('trend', 'line', d, {
                    onClick: (e, elements) => {
                        if (!elements?.length) return;
                        const idx = elements[0].index;
                        const ym = yms[idx];
                        if (ym) self.lwCall('filterFromChart', 'trend', ym);
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: t.text, font: { size: 9 } } },
                        y:  { grid: { color: t.grid }, ticks: { color: t.text, font: { size: 10 }, callback: v => '$'+(v/1e6).toFixed(1)+'M' } },
                        y1: { position: 'right', grid: { display: false }, ticks: { color: t.text, font: { size: 10 }, callback: v => v+'%' }, min: 0, max: 100 },
                    },
                    plugins: {
                        legend: { display: true, position: 'bottom', labels: { boxWidth: 10, color: t.text, font: { size: 10 } } },
                        ...(dlTrend ? { datalabels: dlTrend } : {}),
                    }
                });
            }

            // By UEN
            if (data.by_uen?.datasets?.[0]) {
                const raw = JSON.parse(JSON.stringify(data.by_uen));
                const labels = raw.labels || [];
                const pcts = raw.pcts || [];
                const d = { labels: raw.labels, datasets: raw.datasets };
                d.datasets[0].backgroundColor = mc.blue;
                d.datasets[0].borderWidth = 0;
                const self = this;
                const dlBar = self._hasDatalabels ? self.moneyDatalabels(t, pcts) : false;
                this.upsert('byUen', 'bar', d, {
                    onClick: (e, elements) => {
                        if (!elements?.length) return;
                        const idx = elements[0].index;
                        const v = labels[idx];
                        if (v != null && v !== '') self.lwCall('filterFromChart', 'by_uen', String(v));
                    },
                    layout: self.chartLayoutPadding({ top: 38 }),
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label(ctx) {
                            const p = pcts[ctx.dataIndex];
                            return ' $'+ctx.parsed.y.toLocaleString('es-CO')+(p != null ? ' ('+p+'%)' : '');
                        } } },
                        ...(dlBar ? { datalabels: dlBar } : {}),
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: t.text, font: { size: 10 } } },
                        y: { grid: { color: t.grid }, ticks: { color: t.text, font: { size: 10 }, callback: v => '$'+(v/1e6).toFixed(1)+'M' }, grace: '12%' },
                    },
                });
            }

            // By Channel
            if (data.by_channel?.datasets?.[0]) {
                const raw = JSON.parse(JSON.stringify(data.by_channel));
                const labels = raw.labels || [];
                const pcts = raw.pcts || [];
                const d = { labels: raw.labels, datasets: raw.datasets };
                d.datasets[0].backgroundColor = mc.cyan;
                d.datasets[0].borderWidth = 0;
                const self = this;
                const dlBarCh = self._hasDatalabels ? self.moneyDatalabels(t, pcts) : false;
                this.upsert('byChannel', 'bar', d, {
                    onClick: (e, elements) => {
                        if (!elements?.length) return;
                        const idx = elements[0].index;
                        const v = labels[idx];
                        if (v != null && v !== '') self.lwCall('filterFromChart', 'by_channel', String(v));
                    },
                    layout: self.chartLayoutPadding({ top: 38 }),
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label(ctx) {
                            const p = pcts[ctx.dataIndex];
                            return ' $'+ctx.parsed.y.toLocaleString('es-CO')+(p != null ? ' ('+p+'%)' : '');
                        } } },
                        ...(dlBarCh ? { datalabels: dlBarCh } : {}),
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: t.text, font: { size: 10 } } },
                        y: { grid: { color: t.grid }, ticks: { color: t.text, font: { size: 10 }, callback: v => '$'+(v/1e6).toFixed(1)+'M' }, grace: '12%' },
                    },
                });
            }

            // By Advisor (horizontal)
            if (data.by_advisor?.datasets?.[0]) {
                const raw = JSON.parse(JSON.stringify(data.by_advisor));
                const advisorIds = raw.advisor_ids || [];
                const pcts = raw.pcts || [];
                const d = { labels: raw.labels, datasets: raw.datasets };
                d.datasets[0].backgroundColor = mc.purple;
                d.datasets[0].borderWidth = 0;
                const values = d.datasets[0].data || [];
                const maxVal = values.length ? Math.max(...values.map(v => Number(v) || 0)) : 0;
                const self = this;
                const dlHBar = self._hasDatalabels ? self.horizontalBarMoneyLabels(t, pcts) : false;
                if (dlHBar) {
                    d.datasets[0].datalabels = dlHBar;
                }
                this.upsert('byAdvisor', 'bar', d, {
                    indexAxis: 'y',
                    onClick: (e, elements) => {
                        if (!elements?.length) return;
                        const idx = elements[0].index;
                        const aid = advisorIds[idx];
                        if (aid) self.lwCall('filterFromChart', 'by_advisor', String(aid));
                    },
                    layout: self.chartLayoutPadding({ right: 112, left: 12 }),
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label(ctx) {
                            const p = pcts[ctx.dataIndex];
                            return ' $'+ctx.parsed.x.toLocaleString('es-CO')+(p != null ? ' ('+p+'%)' : '');
                        } } },
                        ...(dlHBar ? { datalabels: dlHBar } : {}),
                    },
                    scales: {
                        x: {
                            grid: { color: t.grid },
                            ticks: { color: t.text, font: { size: 10 }, callback: v => '$'+(v/1e6).toFixed(1)+'M' },
                            grace: '20%',
                            suggestedMax: maxVal > 0 ? maxVal * 1.22 : undefined,
                        },
                        y: {
                            grid: { display: false },
                            ticks: {
                                color: t.text,
                                font: { size: 10 },
                                autoSkip: false,
                                callback(value) {
                                    const lbl = this.getLabelForValue(value) ?? '';
                                    return String(lbl).length > 32 ? String(lbl).slice(0, 30) + '…' : lbl;
                                },
                            },
                        },
                    },
                }, true);
            }

            // Pareto
            if (data.pareto?.datasets?.[0]) {
                const raw = JSON.parse(JSON.stringify(data.pareto));
                const clientIds = raw.client_ids || [];
                const d = { labels: raw.labels, datasets: raw.datasets };
                d.datasets[0].backgroundColor = mc.blue;
                d.datasets[0].borderWidth = 0;
                if (d.datasets[1]) { d.datasets[1].borderColor = mc.amber; d.datasets[1].pointBackgroundColor = mc.amber; d.datasets[1].backgroundColor = 'rgba(245,158,11,.07)'; d.datasets[1].tension = 0.3; d.datasets[1].borderWidth = 2; }
                const self = this;
                if (self._hasDatalabels) {
                    d.datasets[0].datalabels = Object.assign(self.moneyDatalabels(t), { font: { size: 8, weight: '700' } });
                    if (d.datasets[1]) {
                        d.datasets[1].datalabels = {
                            clip: false,
                            anchor: 'end',
                            align: 'top',
                            offset: 6,
                            color: mc.amber,
                            font: { size: 8, weight: '700' },
                            formatter: (v) => Number(v).toFixed(0) + '%',
                        };
                    }
                }
                try {
                    if (this._instances['pareto']) { try { this._instances['pareto'].destroy(); } catch(e) {} }
                    this._instances['pareto'] = new Chart(this.$refs['pareto'], {
                        data: d,
                        options: {
                            responsive: true, maintainAspectRatio: false, animation: false,
                            layout: self.chartLayoutPadding({ top: 34 }),
                            onClick: (e, elements) => {
                                if (!elements?.length) return;
                                const el = elements[0];
                                if (el.datasetIndex !== 0) return;
                                const cid = clientIds[el.index];
                                if (cid) self.lwCall('filterFromChart', 'pareto', String(cid));
                            },
                            plugins: { legend: { display: true, position: 'bottom', labels: { boxWidth: 10, color: t.text, font: { size: 10 } } } },
                            scales: {
                                x:  { grid: { display: false }, ticks: { color: t.text, font: { size: 9 }, maxRotation: 25 } },
                                y:  { position: 'left',  grid: { color: t.grid }, ticks: { color: t.text, callback: v => '$'+(v/1e6).toFixed(1)+'M', font: { size: 10 } }, grace: '15%' },
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
