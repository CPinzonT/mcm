<x-filament-panels::page>

@include('filament.pages.partials.modern-dashboard-styles')

@push('styles')
<style>
.reports-page .rp-filter-grid {
    align-items: end;
    display: grid;
    gap: 0.85rem;
    grid-template-columns: minmax(14rem, 1.1fr) minmax(9rem, 0.6fr) minmax(9rem, 0.6fr) minmax(10rem, 0.7fr) auto auto;
}

.reports-page .rp-summary-bar {
    align-items: center;
    background: var(--mcm-surface);
    border: 1px solid var(--mcm-border);
    border-radius: 12px;
    display: flex;
    gap: 2rem;
    justify-content: space-between;
    padding: 1rem 1.5rem;
}

.reports-page .rp-summary-group { display: flex; align-items: baseline; gap: 0.55rem; }
.reports-page .rp-summary-value { color: var(--mcm-text-strong); font-size: 1.45rem; font-weight: 600; letter-spacing: -0.02em; line-height: 1; }
.reports-page .rp-summary-label { color: var(--mcm-muted); font-size: 0.78rem; font-weight: 500; }

.reports-page .rp-table-wrap {
    background: var(--mcm-surface);
    border: 1px solid var(--mcm-border);
    border-radius: 12px;
    overflow: hidden;
}


.reports-page .rp-table-head {
    align-items: center;
    border-bottom: 1px solid var(--mcm-border);
    display: flex;
    gap: 1rem;
    justify-content: space-between;
    padding: 1rem 1.5rem;
}

.reports-page .rp-table-title { color: var(--mcm-text-strong); font-size: 0.95rem; font-weight: 600; }
.reports-page .rp-table-scroll { max-height: 60vh; overflow: auto; }

.reports-page .rp-empty {
    align-items: center;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 4rem 1.5rem;
    text-align: center;
}

.reports-page .rp-empty svg { color: var(--mcm-soft); height: 3rem; opacity: 0.45; width: 3rem; }
.reports-page .rp-empty-title { color: var(--mcm-text-strong); font-size: 0.95rem; font-weight: 600; }
.reports-page .rp-empty-copy { color: var(--mcm-muted); font-size: 0.82rem; max-width: 26rem; line-height: 1.55; }

.reports-page .rp-loading-overlay {
    align-items: center;
    background: color-mix(in srgb, var(--mcm-surface) 88%, transparent);
    backdrop-filter: blur(2px);
    display: flex;
    inset: 0;
    justify-content: center;
    position: absolute;
    z-index: 10;
    border-radius: 12px;
}

.reports-page .rp-spinner {
    border: 3px solid var(--mcm-border);
    border-radius: 50%;
    height: 2rem;
    width: 2rem;
}

.reports-page .rp-hero-meta { display: flex; align-items: center; gap: 0.85rem; }
.reports-page .rp-type-pill {
    background: var(--mcm-accent-soft);
    border: 1px solid var(--mcm-border);
    border-radius: 9999px;
    color: var(--mcm-text-strong);
    font-size: 0.78rem;
    font-weight: 600;
    padding: 0.3rem 0.75rem;
}

@media (max-width: 1200px) { .reports-page .rp-filter-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (max-width: 760px) {
    .reports-page .rp-filter-grid { grid-template-columns: 1fr; }
    .reports-page .rp-summary-bar { flex-direction: column; align-items: flex-start; gap: 0.75rem; }
}
</style>
@endpush

@php
$reportLabels = [
    'cartera_regional'     => 'Cartera por Regional',
    'cartera_canal'        => 'Cartera por Canal',
    'cartera_gestor'       => 'Cartera por Asesor',
    'promesas_pendientes'  => 'Promesas Pendientes',
    'promesas_incumplidas' => 'Promesas Incumplidas',
    'gestiones_gestor'     => 'Gestiones por Asesor',
    'analisis_vencimiento' => 'Análisis de Vencimiento',
];
$currentLabel = $reportLabels[$this->reportType] ?? null;
$hasData = $currentLabel && count($this->rows ?? []) > 0;
$hasGenerated = $currentLabel && isset($this->rows);
@endphp

<div class="mcm-modern-page reports-page space-y-5">

    <section class="page-hero mcm-reveal">
        <div>
            <p class="dash-section-title" style="margin-bottom:0.3rem;">Inteligencia</p>
            <h1>Reportes</h1>
            <p>Genera y exporta reportes operativos de cartera, recaudos y gestión de cobro.</p>
        </div>
        @if($currentLabel)
        <div class="rp-hero-meta"><span class="rp-type-pill">{{ $currentLabel }}</span></div>
        @endif
    </section>

    <section class="filter-bar mcm-reveal">
        <div class="rp-filter-grid">
            <div><p class="filter-label">Tipo de reporte</p><select wire:model="reportType" class="filter-input"><option value="">— Seleccionar —</option>@foreach($reportLabels as $val => $lbl)<option value="{{ $val }}">{{ $lbl }}</option>@endforeach</select></div>
            <div><p class="filter-label">Período desde</p><input type="month" wire:model="periodFrom" class="filter-input"/></div>
            <div><p class="filter-label">Período hasta</p><input type="month" wire:model="periodTo" class="filter-input"/></div>
            <div><p class="filter-label">UEN</p><select wire:model="uen" class="filter-input"><option value="">Todas</option>@foreach($this->uenOptions as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select></div>
            <button wire:click="generateReport" class="btn-primary" style="align-self:flex-end"><x-heroicon-o-play style="width:1rem;height:1rem"/>Generar</button>
            @if($hasData)
            <a href="{{ route('admin.exports.portfolio', ['period' => $this->periodFrom]) }}" target="_blank" class="btn-ghost" style="align-self:flex-end"><x-heroicon-o-arrow-down-tray style="width:1rem;height:1rem"/>Exportar</a>
            @endif
        </div>
    </section>

    @if($hasGenerated && $hasData)
    <div class="rp-summary-bar mcm-reveal">
        <div class="rp-summary-group"><span class="rp-summary-value">{{ number_format($this->summary['total_rows']) }}</span><span class="rp-summary-label">registros</span></div>
        @if(($this->summary['total_amount'] ?? 0) > 0)
        <div class="rp-summary-group"><span class="rp-summary-value">${{ number_format($this->summary['total_amount'], 0, ',', '.') }}</span><span class="rp-summary-label">total</span></div>
        @endif
        <div style="margin-left:auto"><span class="badge-pill badge-blue">{{ $currentLabel }}</span></div>
    </div>
    @endif

    <div class="rp-table-wrap mcm-reveal" style="position:relative">
        <div class="rp-loading-overlay" wire:loading wire:target="generateReport"><div class="rp-spinner"></div></div>
        <div class="rp-table-head">
            <div class="rp-table-title">{{ $currentLabel ?? 'Resultados' }}</div>
            @if($hasData)<span style="color:var(--mcm-muted);font-size:.78rem;">{{ number_format($this->summary['total_rows']) }} filas</span>@endif
        </div>

        @if(!$hasGenerated)
        <div class="rp-empty"><x-heroicon-o-chart-bar-square/><p class="rp-empty-title">Configura y genera un reporte</p><p class="rp-empty-copy">Selecciona el tipo de reporte, el rango de período y haz clic en <strong>Generar</strong> para ver los resultados.</p></div>
        @elseif(!$hasData)
        <div class="rp-empty"><x-heroicon-o-magnifying-glass/><p class="rp-empty-title">Sin resultados</p><p class="rp-empty-copy">No se encontraron datos para los filtros seleccionados. Ajusta el período o la UEN.</p></div>
        @else
        <div class="rp-table-scroll">
            <table class="data-table" style="width:100%">
                <thead><tr>@foreach($this->columns as $col)<th>{{ $col['label'] }}</th>@endforeach</tr></thead>
                <tbody>
                    @foreach($this->rows as $row)
                    <tr>
                        @foreach($this->columns as $col)
                        <td>
                            @php $val = is_array($row) ? ($row[$col['key']] ?? '—') : ($row->{$col['key']} ?? '—'); @endphp
                            @if(is_numeric($val) && $val > 1000) ${{ number_format((float)$val, 0, ',', '.') }} @else {{ $val ?? '—' }} @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

</x-filament-panels::page>
