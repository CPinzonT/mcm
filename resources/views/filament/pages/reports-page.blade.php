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

.reports-page .rp-drill-btn {
    align-items: center;
    color: var(--mcm-text-strong);
    display: inline-flex;
    font-size: .78rem;
    font-weight: 650;
    gap: .45rem;
    text-align: left;
}
.reports-page .rp-drill-caret {
    color: var(--mcm-accent);
    display: inline-block;
    font-size: .78rem;
    width: .8rem;
}
.reports-page .rp-drill-row > td { background: var(--mcm-surface-soft); padding: .75rem 1rem !important; }
.reports-page .rp-drill-panel {
    border-left: 3px solid var(--mcm-accent);
    display: grid;
    gap: .65rem;
    padding-left: .75rem;
}
.reports-page .rp-drill-table {
    border: 1px solid var(--mcm-border);
    border-collapse: separate;
    border-radius: 8px;
    border-spacing: 0;
    font-size: .72rem;
    overflow: hidden;
    width: 100%;
}
.reports-page .rp-drill-table th {
    background: color-mix(in srgb, var(--mcm-surface-strong) 72%, transparent);
    color: var(--mcm-muted);
    font-size: .63rem;
    letter-spacing: .04em;
    padding: .45rem .55rem;
    text-transform: uppercase;
}
.reports-page .rp-drill-table td {
    background: var(--mcm-surface);
    border-top: 1px solid var(--mcm-border);
    color: var(--mcm-text);
    padding: .48rem .55rem;
    vertical-align: top;
}
.reports-page .rp-level-documents,
.reports-page .rp-level-managements {
    background: var(--mcm-surface-soft);
    border-left: 2px solid var(--mcm-border-strong);
    padding: .65rem;
}
.reports-page .rp-muted { color: var(--mcm-muted); font-size: .68rem; }
.reports-page .rp-description { max-width: 28rem; white-space: normal; }
.reports-page .rp-row-export {
    color: var(--mcm-accent);
    display: inline-block;
    font-size: .65rem;
    font-weight: 650;
    margin-left: 1.25rem;
    margin-top: .2rem;
    text-decoration: none;
}
.reports-page .rp-row-export:hover { text-decoration: underline; }
.reports-page .rp-management-btn {
    margin-top: .3rem;
    padding: .2rem .45rem;
    border: 1px solid var(--mcm-border);
    border-radius: 6px;
    background: var(--mcm-surface);
    color: var(--mcm-accent);
    cursor: pointer;
    font-size: .64rem;
    font-weight: 700;
}
.reports-page .rp-management-btn:hover { background: var(--mcm-accent-soft); }
.reports-page .cv-mgmt-backdrop {
    align-items: center;
    background: rgba(15, 23, 42, .45);
    display: flex;
    inset: 0;
    justify-content: center;
    padding: 1rem;
    position: fixed;
    z-index: 60;
}
.reports-page .cv-mgmt-modal {
    background: var(--mcm-surface);
    border: 1px solid var(--mcm-border);
    border-radius: 14px;
    box-shadow: 0 18px 48px rgba(15, 23, 42, .18);
    display: flex;
    flex-direction: column;
    max-height: 90vh;
    max-width: 42rem;
    overflow: hidden;
    width: 100%;
}
.reports-page .cv-mgmt-head { align-items: flex-start; border-bottom: 1px solid var(--mcm-border); display: flex; gap: 1rem; justify-content: space-between; padding: 1rem 1.25rem; }
.reports-page .cv-mgmt-kicker { color: var(--mcm-muted); font-size: .68rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; }
.reports-page .cv-mgmt-title { color: var(--mcm-text-strong); font-size: 1.05rem; font-weight: 700; margin-top: .2rem; }
.reports-page .cv-mgmt-sub { color: var(--mcm-muted); font-size: .76rem; margin-top: .25rem; }
.reports-page .cv-mgmt-body { overflow: auto; padding: 1rem 1.25rem; }
.reports-page .cv-mgmt-types { display: flex; flex-wrap: wrap; gap: .35rem; margin-bottom: .85rem; }
.reports-page .cv-mgmt-type { background: var(--mcm-surface); border: 1px solid var(--mcm-border); border-radius: 999px; color: var(--mcm-muted); cursor: pointer; font-size: .72rem; font-weight: 600; padding: .28rem .65rem; }
.reports-page .cv-mgmt-type.active { background: var(--mcm-accent-soft); border-color: var(--mcm-accent); color: var(--mcm-accent-strong); }
.reports-page .cv-mgmt-grid { display: grid; gap: .65rem; grid-template-columns: 1fr 1fr; }
.reports-page .cv-mgmt-full { grid-column: 1 / -1; }
.reports-page .cv-mgmt-foot { border-top: 1px solid var(--mcm-border); display: flex; gap: .5rem; justify-content: flex-end; padding: .85rem 1.25rem; }
.reports-page .cv-mgmt-error { color: var(--mcm-red); font-size: .72rem; margin-top: .2rem; }

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
    'cartera_gestor'       => 'Cartera y Gestión por Asesor',
    'promesas_pendientes'  => 'Promesas Pendientes',
    'promesas_incumplidas' => 'Promesas Incumplidas',
    'gestiones_gestor'     => 'Gestiones por Asesor',
    'acta_compromisos'     => 'Acta de compromisos',
    'analisis_vencimiento' => 'Análisis de Vencimiento',
    'trazabilidad_documental' => 'Trazabilidad Documental',
];
$isActa = $this->reportType === 'acta_compromisos';
$isTraceability = $this->reportType === 'trazabilidad_documental';
$isAdvisorReport = $this->reportType === 'cartera_gestor';
$exportUrl = $isActa ? $this->exportActaUrl() : null;
$advisorExportUrl = $isAdvisorReport ? $this->advisorReportExportUrl() : null;
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
            <div><p class="filter-label">Tipo de reporte</p><select wire:model.live="reportType" class="filter-input"><option value="">— Seleccionar —</option>@foreach($reportLabels as $val => $lbl)<option value="{{ $val }}">{{ $lbl }}</option>@endforeach</select></div>
            @if($isActa)
            <div><p class="filter-label">Fecha desde</p><input type="date" wire:model="dateFrom" class="filter-input" title="Rango por días"/></div>
            <div><p class="filter-label">Fecha hasta</p><input type="date" wire:model="dateTo" class="filter-input" title="Rango por días"/></div>
            <div><p class="filter-label">Mes desde</p><input type="month" wire:model="periodFrom" class="filter-input" title="Alternativa: rango por meses"/></div>
            <div><p class="filter-label">Mes hasta</p><input type="month" wire:model="periodTo" class="filter-input" title="Alternativa: rango por meses"/></div>
            @elseif(!$isTraceability)
            <div><p class="filter-label">Período desde</p><input type="month" wire:model="periodFrom" class="filter-input"/></div>
            <div><p class="filter-label">Período hasta</p><input type="month" wire:model="periodTo" class="filter-input"/></div>
            @endif
            <div><p class="filter-label">UEN</p><select wire:model.live="uen" class="filter-input"><option value="">Todas</option>@foreach($this->uenOptions as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select></div>
            <div><p class="filter-label">Canal</p><select wire:model.live="channel" class="filter-input"><option value="">Todos</option>@foreach($this->channelOptions as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach</select></div>
            @if($isTraceability)
            <div>
                <p class="filter-label">Cliente</p>
                <select wire:model="clientId" class="filter-input">
                    <option value="">— Seleccionar cliente —</option>
                    @foreach($this->traceabilityClientOptions as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            @if($isActa)
            <div><p class="filter-label">Hora desde (opc.)</p><input type="time" wire:model="timeFrom" class="filter-input"/></div>
            <div><p class="filter-label">Hora hasta (opc.)</p><input type="time" wire:model="timeTo" class="filter-input"/></div>
            @endif
            <button wire:click="generateReport" class="btn-primary" style="align-self:flex-end"><x-heroicon-o-play style="width:1rem;height:1rem"/>Generar</button>
            @if($isActa && $exportUrl)
            <a href="{{ $exportUrl }}" target="_blank" class="btn-ghost" style="align-self:flex-end" title="Descarga Excel del acta de compromisos"><x-heroicon-o-arrow-down-tray style="width:1rem;height:1rem"/>Exportar acta</a>
            @elseif($isAdvisorReport && $advisorExportUrl && $hasData)
            <a href="{{ $advisorExportUrl }}" target="_blank" class="btn-ghost" style="align-self:flex-end" title="Descarga Excel con hojas de cartera y gestiones"><x-heroicon-o-arrow-down-tray style="width:1rem;height:1rem"/>Exportar cartera y gestión</a>
            @elseif(!$isActa && !$isTraceability && $hasData)
            <a href="{{ route('admin.exports.portfolio', ['period' => $this->periodFrom]) }}" target="_blank" class="btn-ghost" style="align-self:flex-end"><x-heroicon-o-arrow-down-tray style="width:1rem;height:1rem"/>Exportar</a>
            @endif
        </div>
        @if($isTraceability)
        <p style="margin-top:.65rem;font-size:.78rem;color:var(--mcm-muted);">
            Se consulta únicamente al presionar <strong style="color:var(--mcm-text-strong);">Generar</strong>.
            Muestra hasta los 100 documentos más recientes del cliente en la cartera activa.
        </p>
        @endif
        @if($isAdvisorReport)
        <p style="margin-top:.65rem;font-size:.78rem;color:var(--mcm-muted);">
            Expande un asesor para ver sus clientes; luego expande cada cliente para consultar documentos y las gestiones asociadas.
            El Excel contiene hojas separadas de <strong style="color:var(--mcm-text-strong);">Resumen Asesores</strong>,
            <strong style="color:var(--mcm-text-strong);">Clientes</strong>, <strong style="color:var(--mcm-text-strong);">Cartera</strong>
            y <strong style="color:var(--mcm-text-strong);">Gestiones</strong>.
        </p>
        @endif
        @if($isActa && $this->actaDateRangeLabel())
        <p style="margin-top:.65rem;font-size:.78rem;color:var(--mcm-muted);">
            Rango del acta: <strong style="color:var(--mcm-text-strong);">{{ $this->actaDateRangeLabel() }}</strong>
            · El Excel exportado usa las mismas gestiones filtradas por fecha de contacto.
        </p>
        @endif
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
        <div class="rp-empty"><x-heroicon-o-chart-bar-square/><p class="rp-empty-title">Configura y genera un reporte</p><p class="rp-empty-copy">Selecciona el tipo de reporte, completa sus filtros y haz clic en <strong>Generar</strong> para ver los resultados.</p></div>
        @elseif(!$hasData)
        <div class="rp-empty"><x-heroicon-o-magnifying-glass/><p class="rp-empty-title">Sin resultados</p><p class="rp-empty-copy">No se encontraron datos para los filtros seleccionados. Ajusta el período o la UEN.</p></div>
        @else
        <div class="rp-table-scroll">
            <table class="data-table" style="width:100%">
                @if($isAdvisorReport)
                <thead>
                    <tr>
                        <th>Asesor</th>
                        <th>Clientes</th>
                        <th>Documentos</th>
                        <th>Gestiones</th>
                        <th>Saldo total</th>
                        <th>Saldo vencido</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($this->rows as $row)
                    @php
                        $advisorKey = (string) (is_array($row) ? $row['advisor_key'] : $row->advisor_key);
                        $advisorExpanded = (bool) ($this->expandedAdvisors[$advisorKey] ?? false);
                        $advisorName = is_array($row) ? $row['advisor'] : $row->advisor;
                    @endphp
                    <tr wire:key="advisor-summary-{{ $advisorKey }}">
                        <td>
                            <button type="button" class="rp-drill-btn" wire:click='toggleAdvisorDrilldown(@json($advisorKey))'>
                                <span class="rp-drill-caret">{{ $advisorExpanded ? '▼' : '▶' }}</span>
                                {{ $advisorName }}
                            </button>
                            <div>
                                <a href="{{ $this->advisorReportExportUrl($advisorKey) }}" target="_blank" class="rp-row-export">
                                    Exportar este asesor
                                </a>
                            </div>
                        </td>
                        <td>{{ number_format((int) (is_array($row) ? $row['clientes'] : $row->clientes), 0, ',', '.') }}</td>
                        <td>{{ number_format((int) (is_array($row) ? $row['documentos'] : $row->documentos), 0, ',', '.') }}</td>
                        <td>{{ number_format((int) (is_array($row) ? $row['gestiones'] : $row->gestiones), 0, ',', '.') }}</td>
                        <td>${{ number_format((float) (is_array($row) ? $row['saldo_total'] : $row->saldo_total), 0, ',', '.') }}</td>
                        <td>${{ number_format((float) (is_array($row) ? $row['saldo_vencido'] : $row->saldo_vencido), 0, ',', '.') }}</td>
                    </tr>

                    @if($advisorExpanded)
                    <tr class="rp-drill-row" wire:key="advisor-detail-{{ $advisorKey }}">
                        <td colspan="6">
                            <div class="rp-drill-panel">
                                <table class="rp-drill-table">
                                    <thead>
                                        <tr>
                                            <th>Cliente</th>
                                            <th>NIT</th>
                                            <th>UEN</th>
                                            <th>Canal</th>
                                            <th>Documentos</th>
                                            <th>Gestiones</th>
                                            <th>Saldo total</th>
                                            <th>Saldo vencido</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($this->advisorClients[$advisorKey] ?? [] as $client)
                                        @php
                                            $clientId = (int) $client['client_id'];
                                            $clientStateKey = $this->advisorClientStateKey($advisorKey, $clientId);
                                            $clientExpanded = (bool) ($this->expandedAdvisorClients[$clientStateKey] ?? false);
                                        @endphp
                                        <tr wire:key="advisor-client-{{ $clientStateKey }}">
                                            <td>
                                                <button type="button" class="rp-drill-btn" wire:click='toggleAdvisorClient(@json($advisorKey), {{ $clientId }})'>
                                                    <span class="rp-drill-caret">{{ $clientExpanded ? '▼' : '▶' }}</span>
                                                    {{ $client['client'] }}
                                                </button>
                                                <div>
                                                    <button type="button"
                                                            class="rp-management-btn"
                                                            wire:click='openManagementModal(@json($advisorKey), {{ $clientId }})'>
                                                        Registrar gestión general
                                                    </button>
                                                </div>
                                            </td>
                                            <td>{{ $client['nit'] ?: '—' }}</td>
                                            <td>{{ $client['uen'] ?: '—' }}</td>
                                            <td>{{ $client['channel'] ?: '—' }}</td>
                                            <td>{{ number_format($client['documentos'], 0, ',', '.') }}</td>
                                            <td>{{ number_format($client['gestiones'], 0, ',', '.') }}</td>
                                            <td>${{ number_format($client['saldo_total'], 0, ',', '.') }}</td>
                                            <td>${{ number_format($client['saldo_vencido'], 0, ',', '.') }}</td>
                                        </tr>

                                        @if($clientExpanded)
                                        <tr wire:key="client-documents-{{ $clientStateKey }}">
                                            <td colspan="8" class="rp-level-documents">
                                                <table class="rp-drill-table">
                                                    <thead>
                                                        <tr>
                                                            <th>Documento</th>
                                                            <th>Tipo</th>
                                                            <th>Emisión / Vencimiento</th>
                                                            <th>Días mora</th>
                                                            <th>Valor original</th>
                                                            <th>Saldo</th>
                                                            <th>Estado</th>
                                                            <th>Gestiones / Última gestión</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($this->clientDocuments[$clientStateKey] ?? [] as $document)
                                                        @php
                                                            $documentKey = (string) $document['document_key'];
                                                            $documentStateKey = $this->documentStateKey($advisorKey, $clientId, $documentKey);
                                                            $documentExpanded = (bool) ($this->expandedDocuments[$documentStateKey] ?? false);
                                                        @endphp
                                                        <tr wire:key="client-document-{{ $documentStateKey }}">
                                                            <td>
                                                                @if($document['management_count'] > 0)
                                                                <button type="button" class="rp-drill-btn" wire:click='toggleDocumentManagements(@json($advisorKey), {{ $clientId }}, @json($documentKey))'>
                                                                    <span class="rp-drill-caret">{{ $documentExpanded ? '▼' : '▶' }}</span>
                                                                    {{ $document['document_number'] }}
                                                                </button>
                                                                @else
                                                                    <span style="padding-left:1.25rem;">{{ $document['document_number'] }}</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ $document['document_type'] }}</td>
                                                            <td>
                                                                {{ $document['issue_date'] ? \Carbon\Carbon::parse($document['issue_date'])->format('d/m/Y') : '—' }}
                                                                <div class="rp-muted">Vence: {{ $document['due_date'] ? \Carbon\Carbon::parse($document['due_date'])->format('d/m/Y') : '—' }}</div>
                                                            </td>
                                                            <td>{{ $document['is_general'] ? '—' : number_format($document['days_overdue'], 0, ',', '.') }}</td>
                                                            <td>{{ $document['is_general'] ? '—' : '$'.number_format($document['original_amount'], 0, ',', '.') }}</td>
                                                            <td>{{ $document['is_general'] ? '—' : '$'.number_format($document['pending_amount'], 0, ',', '.') }}</td>
                                                            <td>{{ $document['is_general'] ? 'Gestión' : $document['status'] }}</td>
                                                            <td>
                                                                {{ number_format($document['management_count'], 0, ',', '.') }}
                                                                <div class="rp-muted">{{ $document['last_management'] ?: 'Sin gestiones' }}</div>
                                                                <button type="button"
                                                                        class="rp-management-btn"
                                                                        wire:click='openManagementModal(@json($advisorKey), {{ $clientId }}, {{ $document['document_id'] ?? 'null' }})'>
                                                                    Registrar gestión
                                                                </button>
                                                            </td>
                                                        </tr>

                                                        @if($documentExpanded)
                                                        <tr wire:key="document-managements-{{ $documentStateKey }}">
                                                            <td colspan="8" class="rp-level-managements">
                                                                <table class="rp-drill-table">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Fecha</th>
                                                                            <th>Tipo</th>
                                                                            <th>Asunto</th>
                                                                            <th>Resultado</th>
                                                                            <th>Promesa</th>
                                                                            <th>Estado</th>
                                                                            <th>Descripción</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @forelse($this->documentManagements[$documentStateKey] ?? [] as $management)
                                                                        <tr wire:key="management-{{ $management['id'] }}">
                                                                            <td>
                                                                                {{ $management['contact_date'] ? \Carbon\Carbon::parse($management['contact_date'])->format('d/m/Y') : '—' }}
                                                                                @if($management['contact_time'])<div class="rp-muted">{{ $management['contact_time'] }}</div>@endif
                                                                            </td>
                                                                            <td>{{ $management['type'] }}</td>
                                                                            <td>{{ $management['subject'] }}</td>
                                                                            <td>{{ $management['result'] ?: '—' }}</td>
                                                                            <td>
                                                                                {{ $management['promised_date'] ? \Carbon\Carbon::parse($management['promised_date'])->format('d/m/Y') : '—' }}
                                                                                @if($management['promised_amount'] !== null)
                                                                                <div class="rp-muted">${{ number_format($management['promised_amount'], 0, ',', '.') }}</div>
                                                                                @endif
                                                                            </td>
                                                                            <td>{{ $management['status'] ?: '—' }}</td>
                                                                            <td class="rp-description">{{ $management['description'] ?: '—' }}</td>
                                                                        </tr>
                                                                        @empty
                                                                        <tr><td colspan="7" class="rp-muted">Sin gestiones registradas.</td></tr>
                                                                        @endforelse
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        @endif
                                                        @empty
                                                        <tr><td colspan="8" class="rp-muted">Sin documentos de cartera para este cliente.</td></tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                        @endif
                                        @empty
                                        <tr><td colspan="8" class="rp-muted">Sin clientes asignados en la cartera activa.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
                @else
                <thead><tr>@foreach($this->columns as $col)<th>{{ $col['label'] }}</th>@endforeach</tr></thead>
                <tbody>
                    @foreach($this->rows as $row)
                    <tr>
                        @foreach($this->columns as $col)
                        <td>
                            @php
                                $val = is_array($row) ? ($row[$col['key']] ?? '—') : ($row->{$col['key']} ?? '—');
                                $format = $col['format'] ?? null;
                            @endphp
                            @if($format === 'money')
                                ${{ number_format((float) $val, 0, ',', '.') }}
                            @elseif($format === 'text')
                                {{ $val ?? '—' }}
                            @elseif(is_numeric($val) && $val > 1000)
                                ${{ number_format((float)$val, 0, ',', '.') }}
                            @else
                                {{ $val ?? '—' }}
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
                @endif
            </table>
        </div>
        @endif
    </div>

    @if($showMgmtModal)
        @include('filament.resources.client-resource.partials.management-modal')
    @endif
</div>

</x-filament-panels::page>
