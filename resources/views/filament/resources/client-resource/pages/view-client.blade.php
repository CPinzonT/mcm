<x-filament-panels::page>
@include('filament.pages.partials.modern-dashboard-styles')

@push('styles')
<style>
/* ── Client View ─────────────────────────────────────────── */

/* Hero identity bar */
.client-view .cv-identity {
    align-items: center;
    background: var(--mcm-surface);
    border: 1px solid var(--mcm-border);
    border-radius: 14px;
    display: flex;
    gap: 1.25rem;
    padding: 1.35rem 1.75rem;
}

.client-view .cv-avatar {
    align-items: center;
    background: var(--mcm-accent-soft);
    border: 1.5px solid color-mix(in srgb,var(--mcm-accent) 22%,var(--mcm-border));
    border-radius: 12px;
    color: var(--mcm-accent-strong);
    display: flex;
    flex-shrink: 0;
    font-size: 1.35rem;
    font-weight: 780;
    height: 3.5rem;
    justify-content: center;
    letter-spacing: -.02em;
    width: 3.5rem;
}

.client-view .cv-identity-body { flex: 1; min-width: 0; }

.client-view .cv-identity-name {
    color: var(--mcm-text-strong);
    font-size: 1.45rem;
    font-weight: 720;
    letter-spacing: -.02em;
    line-height: 1.15;
    overflow-wrap: anywhere;
}

.client-view .cv-identity-meta {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
    margin-top: .42rem;
}

.client-view .cv-identity-code {
    color: var(--mcm-muted);
    font-family: ui-monospace, 'Courier New', monospace;
    font-size: .76rem;
    font-variant-numeric: tabular-nums;
}

.client-view .cv-identity-actions {
    align-items: center;
    display: flex;
    flex-shrink: 0;
    gap: .5rem;
}

/* KPI strip */
.client-view .cv-kpi-strip {
    display: grid;
    gap: .75rem;
    grid-template-columns: repeat(5, minmax(0,1fr));
}
.client-view .cv-kpi-strip.cv-kpi-strip--commercial {
    grid-template-columns: repeat(3, minmax(0,1fr));
    margin-top: .75rem;
}

.client-view .cv-kpi-item {
    background: var(--mcm-surface);
    border: 1px solid var(--mcm-border);
    border-radius: 12px;
    padding: 1.1rem 1.25rem;
}

.client-view .cv-kpi-label {
    color: var(--mcm-muted);
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .05em;
    text-transform: uppercase;
}

.client-view .cv-kpi-val {
    color: var(--mcm-text-strong);
    font-size: 1.3rem;
    font-weight: 720;
    letter-spacing: -.02em;
    line-height: 1.1;
    margin-top: .32rem;
    overflow-wrap: anywhere;
}

.client-view .cv-kpi-val.danger { color: var(--mcm-red); }
.client-view .cv-kpi-val.warn   { color: var(--mcm-amber); }
.client-view .cv-kpi-val.ok     { color: var(--mcm-green); }

.client-view .cv-kpi-sub {
    color: var(--mcm-muted);
    font-size: .68rem;
    margin-top: .2rem;
}

/* Two-col layout */
.client-view .cv-main-grid {
    display: grid;
    gap: .9rem;
    grid-template-columns: minmax(0,1.55fr) minmax(18rem,.75fr);
}

/* Card base */
.client-view .cv-card {
    background: var(--mcm-surface);
    border: 1px solid var(--mcm-border);
    border-radius: 14px;
    overflow: hidden;
}

.client-view .cv-card-head {
    align-items: center;
    background: var(--mcm-surface-soft);
    border-bottom: 1px solid var(--mcm-border);
    display: flex;
    gap: .65rem;
    justify-content: space-between;
    padding: .85rem 1.2rem;
}

.client-view .cv-card-icon {
    align-items: center;
    background: var(--mcm-accent-soft);
    border: 1px solid color-mix(in srgb,var(--mcm-accent) 22%,var(--mcm-border));
    border-radius: 7px;
    color: var(--mcm-accent-strong);
    display: inline-flex;
    flex-shrink: 0;
    height: 1.85rem;
    justify-content: center;
    width: 1.85rem;
}
.client-view .cv-card-icon svg { height:.84rem;width:.84rem; }

.client-view .cv-card-title {
    color: var(--mcm-text-strong);
    flex: 1;
    font-size: .84rem;
    font-weight: 720;
}

.client-view .cv-card-body { padding: 1.2rem; }

/* Data grid */
.client-view .cv-data-grid {
    display: grid;
    gap: .85rem;
    grid-template-columns: repeat(auto-fill, minmax(10.5rem,1fr));
}

.client-view .cv-datum { }

.client-view .cv-datum-label {
    color: var(--mcm-muted);
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .05em;
    margin-bottom: .15rem;
    text-transform: uppercase;
}

.client-view .cv-datum-value {
    color: var(--mcm-text-strong);
    font-size: .84rem;
    font-weight: 500;
    overflow-wrap: anywhere;
}

.client-view .cv-datum-value.mono {
    font-family: ui-monospace,'Courier New',monospace;
    font-variant-numeric: tabular-nums;
    font-size: .8rem;
}

/* Divider */
.client-view .cv-divider {
    border: none;
    border-top: 1px solid var(--mcm-border);
    margin: .9rem 0;
}

/* Classification pills bar */
.client-view .cv-classif {
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
    margin-top: .65rem;
}

.client-view .cv-classif-pill {
    align-items: center;
    background: var(--mcm-surface-soft);
    border: 1px solid var(--mcm-border);
    border-radius: 7px;
    color: var(--mcm-text);
    display: inline-flex;
    font-size: .73rem;
    font-weight: 640;
    gap: .3rem;
    padding: .28rem .65rem;
}

.client-view .cv-classif-pill span { color: var(--mcm-muted); font-size: .65rem; }

/* Contact form */
.client-view .cv-form-row { margin-bottom: .75rem; }
.client-view .cv-form-row:last-child { margin-bottom: 0; }

.client-view .cv-form-label {
    color: var(--mcm-muted);
    display: block;
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .05em;
    margin-bottom: .28rem;
    text-transform: uppercase;
}

.client-view .cv-form-input {
    background: var(--mcm-surface-soft);
    border: 1px solid var(--mcm-border);
    border-radius: 8px;
    color: var(--mcm-text-strong);
    font-size: .84rem;
    outline: none;
    padding: .5rem .75rem;
    width: 100%;
}

.client-view .cv-form-input:focus {
    border-color: var(--mcm-accent);
    box-shadow: 0 0 0 2.5px var(--mcm-accent-soft);
}

/* Management modal */
.cv-mgmt-backdrop {
    align-items: center;
    background: rgba(15, 23, 42, .45);
    display: flex;
    inset: 0;
    justify-content: center;
    padding: 1rem;
    position: fixed;
    z-index: 60;
}
.cv-mgmt-modal {
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
.cv-mgmt-head {
    align-items: flex-start;
    border-bottom: 1px solid var(--mcm-border);
    display: flex;
    gap: 1rem;
    justify-content: space-between;
    padding: 1rem 1.25rem;
}
.cv-mgmt-kicker { color: var(--mcm-muted); font-size: .68rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; }
.cv-mgmt-title { color: var(--mcm-text-strong); font-size: 1.05rem; font-weight: 700; margin-top: .2rem; }
.cv-mgmt-sub { color: var(--mcm-muted); font-size: .76rem; margin-top: .25rem; }
.cv-mgmt-body { overflow: auto; padding: 1rem 1.25rem; }
.cv-mgmt-types { display: flex; flex-wrap: wrap; gap: .35rem; margin-bottom: .85rem; }
.cv-mgmt-type {
    background: var(--mcm-surface-2, var(--mcm-surface));
    border: 1px solid var(--mcm-border);
    border-radius: 999px;
    color: var(--mcm-muted);
    cursor: pointer;
    font-size: .72rem;
    font-weight: 600;
    padding: .28rem .65rem;
}
.cv-mgmt-type.active { background: var(--mcm-accent-soft); border-color: var(--mcm-accent); color: var(--mcm-accent-strong); }
.cv-mgmt-grid { display: grid; gap: .65rem; grid-template-columns: 1fr 1fr; }
.cv-mgmt-full { grid-column: 1 / -1; }
.cv-mgmt-foot {
    border-top: 1px solid var(--mcm-border);
    display: flex;
    gap: .5rem;
    justify-content: flex-end;
    padding: .85rem 1.25rem;
}
.cv-mgmt-error { color: var(--mcm-red); font-size: .72rem; margin-top: .2rem; }

/* Portfolio table */
.client-view .cv-doc-link {
    color: var(--mcm-accent-strong);
    text-decoration: none;
}

.client-view .cv-doc-link:hover {
    color: var(--mcm-accent);
    text-decoration: underline;
}

.client-view .cv-doc-search-bar {
    align-items: center;
    background: var(--mcm-surface);
    border-bottom: 1px solid var(--mcm-border);
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
    padding: .65rem 1.2rem;
}

.client-view .cv-doc-search-bar .cv-doc-search-input {
    flex: 1;
    min-width: 12rem;
}

.client-view .cv-doc-search-hint {
    color: var(--mcm-muted);
    font-size: .72rem;
    width: 100%;
}

.client-view .cv-table-wrap {
    overflow-x: auto;
    scrollbar-width: thin;
}

/* Paginación documentos — Los SVG de Tailwind/Laravel sin tamaño fijo heredan reglas de .mcm-modern-page y se ven gigantes */
.client-view .cv-docs-pagination {
    align-items: center;
    border-top: 1px solid var(--mcm-border);
    display: flex;
    flex-wrap: wrap;
    gap: .5rem;
    justify-content: flex-end;
    padding: .65rem 1.2rem;
}

.client-view .cv-docs-pagination svg {
    flex-shrink: 0;
    height: 1rem !important;
    max-height: 1rem;
    max-width: 1rem;
    vertical-align: middle;
    width: 1rem !important;
}

.client-view .cv-docs-pagination nav {
    align-items: center;
    display: flex;
    flex-wrap: wrap;
    gap: .25rem;
    justify-content: flex-end;
}

.client-view .cv-docs-pagination a,
.client-view .cv-docs-pagination span {
    align-items: center;
    display: inline-flex !important;
    justify-content: center;
    line-height: 1;
    min-height: 2rem;
    min-width: 2rem;
}

.client-view .cv-mora-bar {
    background: var(--mcm-border);
    border-radius: 2px;
    height: 4px;
    min-width: 3rem;
    overflow: hidden;
    width: 4.5rem;
}

.client-view .cv-mora-fill {
    border-radius: 2px;
    height: 100%;
    max-width: 100%;
}

/* Timeline */
.client-view .cv-tl-list { display: flex; flex-direction: column; }

.client-view .cv-tl-item {
    border-bottom: 1px solid var(--mcm-border);
    display: grid;
    gap: .75rem;
    grid-template-columns: auto minmax(0,1fr);
    padding: .85rem 1.2rem;
}

.client-view .cv-tl-item:last-child { border-bottom: none; }

.client-view .cv-tl-type-dot {
    align-items: center;
    border-radius: 8px;
    display: flex;
    flex-shrink: 0;
    height: 1.7rem;
    justify-content: center;
    margin-top: .05rem;
    width: 1.7rem;
}

.client-view .cv-tl-type-dot svg { height: .78rem; width: .78rem; }

.client-view .cv-tl-row {
    align-items: baseline;
    display: flex;
    gap: .55rem;
    justify-content: space-between;
}

.client-view .cv-tl-type  { color:var(--mcm-text-strong);font-size:.82rem;font-weight:720; }
.client-view .cv-tl-date  { color:var(--mcm-muted);font-size:.71rem;flex-shrink:0; }
.client-view .cv-tl-subj  { color:var(--mcm-text);font-size:.8rem;font-weight:600;margin-top:.18rem; }
.client-view .cv-tl-desc  { color:var(--mcm-muted);font-size:.77rem;line-height:1.5;margin-top:.14rem; }
.client-view .cv-tl-meta  { color:var(--mcm-muted);font-size:.71rem;margin-top:.24rem; }

/* Empty */
.client-view .cv-empty {
    align-items: center;
    display: flex;
    flex-direction: column;
    gap: .65rem;
    padding: 3rem 2rem;
    text-align: center;
}
.client-view .cv-empty svg { color:var(--mcm-muted);height:2.25rem;opacity:.4;width:2.25rem; }
.client-view .cv-empty p   { color:var(--mcm-muted);font-size:.81rem; }

@media (max-width: 1000px) {
    .client-view .cv-kpi-strip   { grid-template-columns: repeat(3,minmax(0,1fr)); }
    .client-view .cv-kpi-strip.cv-kpi-strip--commercial { grid-template-columns: repeat(3,minmax(0,1fr)); }
    .client-view .cv-main-grid   { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .client-view .cv-kpi-strip   { grid-template-columns: repeat(2,minmax(0,1fr)); }
    .client-view .cv-kpi-strip.cv-kpi-strip--commercial { grid-template-columns: 1fr; }
    .client-view .cv-identity     { flex-direction: column; align-items: flex-start; }
    .client-view .cv-identity-actions { width: 100%; }
    .client-view .cv-identity-actions a { flex: 1; }
}
</style>
@endpush

@php
    $c  = $this->record;
    $ps = $this->portfolioSummary;
    $ckpis = $this->clientCommercialKpis;
    $docs     = $this->documents;
    $timeline = $this->timeline;
    $exportPortfolioUrl = $this->exportPortfolioUrl();

    $initials = collect(explode(' ', $c->name))->filter()->take(2)->map(fn($w) => mb_substr($w,0,1))->implode('');

    $typeColors = [
        'call'      => ['bg' => 'color-mix(in srgb,var(--mcm-accent) 12%,var(--mcm-surface-soft))',  'ico' => 'var(--mcm-accent-strong)'],
        'email'     => ['bg' => 'color-mix(in srgb,#8B5CF6 10%,var(--mcm-surface-soft))',            'ico' => '#8B5CF6'],
        'visit'     => ['bg' => 'color-mix(in srgb,var(--mcm-green) 12%,var(--mcm-surface-soft))',   'ico' => 'var(--mcm-green)'],
        'agreement' => ['bg' => 'color-mix(in srgb,var(--mcm-amber) 12%,var(--mcm-surface-soft))',   'ico' => 'var(--mcm-amber)'],
        'legal'     => ['bg' => 'color-mix(in srgb,var(--mcm-red) 12%,var(--mcm-surface-soft))',     'ico' => 'var(--mcm-red)'],
    ];
    $defaultTypeStyle = ['bg' => 'var(--mcm-surface-soft)', 'ico' => 'var(--mcm-muted)'];

    $typeIcons = [
        'call'      => 'heroicon-o-phone',
        'email'     => 'heroicon-o-envelope',
        'visit'     => 'heroicon-o-map-pin',
        'agreement' => 'heroicon-o-document-check',
        'legal'     => 'heroicon-o-scale',
    ];
    $defaultIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    $resultLabels = [
        'contacted'    => 'Contactado',
        'no_answer'    => 'Sin respuesta',
        'promise'      => 'Promesa de pago',
        'paid'         => 'Pago realizado',
        'partial'      => 'Abono parcial',
        'refused'      => 'Rechazado',
        'wrong_number' => 'Número errado',
        'other'        => 'Otro',
    ];

    $riskBadges = [
        'critical' => ['pill' => 'badge-red',   'label' => 'Crítico'],
        'high'     => ['pill' => 'badge-red',   'label' => 'Alto'],
        'medium'   => ['pill' => 'badge-amber', 'label' => 'Medio'],
        'low'      => ['pill' => 'badge-amber', 'label' => 'Bajo'],
        'normal'   => ['pill' => 'badge-gray',  'label' => 'Normal'],
    ];

    $maxOverdueDenominator = max($ps['max_overdue'], 180, 1);
@endphp

<div class="mcm-modern-page client-view space-y-4">

    {{-- ── Identity bar ── --}}
    <div class="cv-identity mcm-reveal">
        <div class="cv-avatar">{{ $initials }}</div>
        <div class="cv-identity-body">
            <div class="cv-identity-name">{{ $c->name }}</div>
            <div class="cv-identity-meta">
                @if($c->document_number)
                    <span class="cv-identity-code">NIT {{ $c->document_number }}</span>
                @endif
                @if(!$c->active)
                    <span class="badge-pill badge-red">Inactivo</span>
                @else
                    <span class="badge-pill badge-green">Activo</span>
                @endif
                @if($c->region)   <span class="badge-pill badge-blue">{{ $c->region }}</span>   @endif
                @if($c->channel)  <span class="badge-pill badge-gray">{{ $c->channel }}</span>  @endif
                @if($c->uen)      <span class="badge-pill badge-gray">UEN: {{ $c->uen }}</span> @endif
            </div>
        </div>
        <div class="cv-identity-actions">
            @if($exportPortfolioUrl)
                <a href="{{ $exportPortfolioUrl }}" target="_blank" rel="noopener" class="btn-ghost">
                    <x-heroicon-o-arrow-down-tray style="width:.9rem;height:.9rem"/>
                    Exportar cartera
                </a>
            @endif
            <a href="{{ route('filament.admin.resources.clients.edit', ['record' => $c->id]) }}"
               class="btn-ghost">
                <x-heroicon-o-pencil-square style="width:.9rem;height:.9rem"/>
                Editar
            </a>
        </div>
    </div>

    {{-- ── KPI Strip ── --}}
    <div class="cv-kpi-strip mcm-stagger">
        <div class="cv-kpi-item">
            <p class="cv-kpi-label">Saldo total</p>
            <p class="cv-kpi-val mono">${{ number_format($ps['total_balance'], 0, ',', '.') }}</p>
            <p class="cv-kpi-sub">
                {{ $ps['total_docs'] }} documento{{ $ps['total_docs'] != 1 ? 's' : '' }}
                @if(!empty($ps['cut_date']))
                    · corte {{ \Carbon\Carbon::parse($ps['cut_date'])->translatedFormat('M Y') }}
                @endif
                @if(!empty($ps['consultation_date']))
                    · mora al {{ \Carbon\Carbon::parse($ps['consultation_date'])->format('d/m/Y') }}
                @endif
            </p>
        </div>
        <div class="cv-kpi-item">
            <p class="cv-kpi-label">Saldo vencido</p>
            <p class="cv-kpi-val mono {{ $ps['overdue_balance'] > 0 ? 'danger' : 'ok' }}">${{ number_format($ps['overdue_balance'], 0, ',', '.') }}</p>
            <p class="cv-kpi-sub">{{ $ps['overdue_docs'] }} en mora</p>
        </div>
        <div class="cv-kpi-item">
            <p class="cv-kpi-label">Documentos activos</p>
            <p class="cv-kpi-val">{{ $ps['total_docs'] }}</p>
            <p class="cv-kpi-sub">{{ $ps['overdue_docs'] }} vencidos</p>
        </div>
        <div class="cv-kpi-item">
            <p class="cv-kpi-label">Mora máxima</p>
            <p class="cv-kpi-val {{ $ps['max_overdue'] > 90 ? 'danger' : ($ps['max_overdue'] > 30 ? 'warn' : 'ok') }}">
                {{ $ps['max_overdue'] }}<small style="font-size:.7em;font-weight:400;color:var(--mcm-muted);">d</small>
            </p>
            <p class="cv-kpi-sub">desde vencimiento · al {{ \Carbon\Carbon::parse($ps['consultation_date'] ?? today())->format('d/m/Y') }}</p>
        </div>
        <div class="cv-kpi-item">
            <p class="cv-kpi-label">Gestiones</p>
            <p class="cv-kpi-val">{{ count($timeline) }}</p>
            <p class="cv-kpi-sub">en historial</p>
        </div>
    </div>

    {{-- Rotación / plazo / cupo (cartera activa + recaudo mes en curso) --}}
    <div class="cv-kpi-strip cv-kpi-strip--commercial mcm-stagger">
        <div class="cv-kpi-item">
            <p class="cv-kpi-label">Cupo</p>
            <p class="cv-kpi-val mono">${{ number_format($ckpis['cupo'], 0, ',', '.') }}</p>
            <p class="cv-kpi-sub">
                @if($ckpis['cupo_es_maestro'])
                    Cupo asignado en maestro SAP
                @else
                    Referencia: suma de montos originales en cartera activa
                @endif
            </p>
        </div>
        <div class="cv-kpi-item">
            <p class="cv-kpi-label">Rotación</p>
            @if($ckpis['rotation_days'] !== null)
                <p class="cv-kpi-val {{ $ckpis['plazo_days'] && $ckpis['rotation_days'] > $ckpis['plazo_days'] ? 'warn' : '' }}">
                    {{ number_format($ckpis['rotation_days'], 1, ',', '.') }}<small style="font-size:.65em;font-weight:500;color:var(--mcm-muted);">d</small>
                </p>
                <p class="cv-kpi-sub">Días reales de rotación (cartera / recaudo de la carga activa)</p>
            @else
                <p class="cv-kpi-val" style="font-size:.95rem;color:var(--mcm-muted);">—</p>
                <p class="cv-kpi-sub">Sin recaudo en el mes o sin cartera</p>
            @endif
        </div>
        <div class="cv-kpi-item">
            <p class="cv-kpi-label">Plazo</p>
            @if($ckpis['plazo_days'] !== null)
                <p class="cv-kpi-val">{{ (int) $ckpis['plazo_days'] }}<small style="font-size:.65em;font-weight:500;color:var(--mcm-muted);">d</small></p>
                <p class="cv-kpi-sub">Plazo contratado (vencimiento − emisión)</p>
            @else
                <p class="cv-kpi-val" style="font-size:.95rem;color:var(--mcm-muted);">—</p>
                <p class="cv-kpi-sub">Sin plazo en maestro ni en documentos</p>
            @endif
        </div>
    </div>

    {{-- ── Profile + Contact ── --}}
    <div class="cv-main-grid">

        {{-- LEFT: Profile info --}}
        <div class="cv-card mcm-reveal">
            <div class="cv-card-head">
                <span class="cv-card-icon"><x-heroicon-o-identification /></span>
                <span class="cv-card-title">Información del cliente</span>
            </div>
            <div class="cv-card-body">
                <div class="cv-data-grid">
                    <div class="cv-datum">
                        <div class="cv-datum-label">Razón social</div>
                        <div class="cv-datum-value">{{ $c->name }}</div>
                    </div>
                    <div class="cv-datum">
                        <div class="cv-datum-label">NIT</div>
                        <div class="cv-datum-value mono">{{ $c->document_number ?? '—' }}</div>
                    </div>
                    <div class="cv-datum">
                        <div class="cv-datum-label">Correo</div>
                        <div class="cv-datum-value">{{ $c->email ?? '—' }}</div>
                    </div>
                    <div class="cv-datum">
                        <div class="cv-datum-label">Teléfono</div>
                        <div class="cv-datum-value">{{ $c->phone ?? '—' }}</div>
                    </div>
                    <div class="cv-datum">
                        <div class="cv-datum-label">Ciudad</div>
                        <div class="cv-datum-value">{{ $c->city ?? '—' }}</div>
                    </div>
                    <div class="cv-datum" style="grid-column: 1 / -1;">
                        <div class="cv-datum-label">Dirección</div>
                        <div class="cv-datum-value">{{ $c->address ?? '—' }}</div>
                    </div>
                </div>

                <hr class="cv-divider">

                <div class="cv-datum-label" style="margin-bottom:.5rem;">Clasificación comercial</div>
                <div class="cv-classif">
                    @if($c->region)
                    <span class="cv-classif-pill"><span>Regional</span> {{ $c->region }}</span>
                    @endif
                    @if($c->channel)
                    <span class="cv-classif-pill"><span>Canal</span> {{ $c->channel }}</span>
                    @endif
                    @if($c->uen)
                    <span class="cv-classif-pill"><span>UEN</span> {{ $c->uen }}</span>
                    @endif
                    @if(!$c->region && !$c->channel && !$c->uen)
                    <span style="color:var(--mcm-muted);font-size:.78rem;">Sin clasificación registrada</span>
                    @endif
                </div>

                @if($c->notes)
                <hr class="cv-divider">
                <div class="cv-datum-label">Observaciones</div>
                <p style="color:var(--mcm-text);font-size:.8rem;line-height:1.55;margin-top:.28rem;">{{ $c->notes }}</p>
                @endif
            </div>
        </div>

        {{-- RIGHT: Contact --}}
        <div class="cv-card mcm-reveal">
            <div class="cv-card-head">
                <span class="cv-card-icon"><x-heroicon-o-user-circle /></span>
                <span class="cv-card-title">Contacto responsable</span>
            </div>
            <div class="cv-card-body">
                <form wire:submit.prevent="saveContact">
                    <div class="cv-form-row">
                        <label class="cv-form-label">Nombre</label>
                        <input wire:model="contactName" type="text" class="cv-form-input"
                               placeholder="Nombre del contacto"/>
                    </div>
                    <div class="cv-form-row">
                        <label class="cv-form-label">Correo</label>
                        <input wire:model="contactEmail" type="email" class="cv-form-input"
                               placeholder="correo@empresa.com"/>
                    </div>
                    <div class="cv-form-row">
                        <label class="cv-form-label">Teléfono</label>
                        <input wire:model="contactPhone" type="text" class="cv-form-input"
                               placeholder="+57 300 000 0000"/>
                    </div>
                    <button type="submit" class="btn-primary" style="margin-top:.85rem;width:100%;">
                        <x-heroicon-o-check style="width:.9rem;height:.9rem"/>
                        Guardar contacto
                    </button>
                </form>

                @if($c->contact_name || $c->contact_email || $c->contact_phone)
                <hr class="cv-divider">
                <div class="cv-datum-label" style="margin-bottom:.55rem;">Contacto actual</div>
                @if($c->contact_name)
                <p style="font-size:.8rem;color:var(--mcm-text);font-weight:640;">{{ $c->contact_name }}</p>
                @endif
                @if($c->contact_email)
                <p style="font-size:.77rem;color:var(--mcm-muted);margin-top:.18rem;">{{ $c->contact_email }}</p>
                @endif
                @if($c->contact_phone)
                <p style="font-size:.77rem;color:var(--mcm-muted);margin-top:.1rem;">{{ $c->contact_phone }}</p>
                @endif
                @endif
            </div>
        </div>

    </div>

    {{-- ── Portfolio Documents ── --}}
    <div class="cv-card mcm-reveal">
        <div class="cv-card-head">
            <span class="cv-card-icon"><x-heroicon-o-document-text /></span>
            <span class="cv-card-title">Documentos de cartera</span>
            <span class="badge-pill badge-{{ $ps['overdue_docs'] > 0 ? 'red' : 'gray' }}">
                {{ $ps['overdue_docs'] }} en mora · {{ $ps['total_docs'] }} activos
            </span>
        </div>
        <div class="cv-doc-search-bar">
            <input type="search"
                   wire:model.live.debounce.300ms="docSearch"
                   class="sd-filter-search cv-doc-search-input"
                   placeholder="Buscar documento (número, referencia o tipo)…"
                   autocomplete="off"
                   spellcheck="false">
            @if(trim($this->docSearch) !== '')
                <button type="button" wire:click="clearDocSearch" class="btn-ghost" style="font-size:.72rem;padding:.35rem .65rem;">
                    Limpiar
                </button>
                <span class="cv-doc-search-hint">
                    {{ $docs->total() }} resultado{{ $docs->total() !== 1 ? 's' : '' }}
                    @if($docs->hasPages())
                        · página {{ $docs->currentPage() }} de {{ $docs->lastPage() }}
                    @endif
                </span>
            @endif
        </div>
        <div class="cv-table-wrap">
            <table class="data-table" style="width:100%;">
                <thead>
                    <tr>
                        <th>Documento</th>
                        <th>Tipo</th>
                        <th>Vencimiento</th>
                        <th style="text-align:right;">Días mora</th>
                        <th style="text-align:right;">Valor original</th>
                        <th style="text-align:right;">Saldo pendiente</th>
                        <th>Riesgo</th>
                        <th>Estado</th>
                        <th style="text-align:center;">Gestión</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($docs as $doc)
                    @php
                        $liveDays = (int) ($doc->live_days_overdue ?? 0);
                        $liveRisk = $this->liveRiskLevelForDocument($doc);
                        $rb = $riskBadges[$liveRisk] ?? ['pill'=>'badge-gray','label'=>$liveRisk ?? '—'];
                        $moraColor = $liveDays > 90
                            ? 'var(--mcm-red)'
                            : ($liveDays > 30 ? 'var(--mcm-amber)' : 'var(--mcm-green)');
                        $moraPct = min(100, round($liveDays / $maxOverdueDenominator * 100));
                    @endphp
                    <tr>
                        <td style="font-family:ui-monospace,'Courier New',monospace;font-size:.77rem;font-weight:700;">
                            <a href="{{ \App\Filament\Resources\PortfolioDocumentResource::getUrl('view', ['record' => $doc->id]) }}"
                               class="cv-doc-link"
                               title="Ver documento de cartera">
                                {{ $doc->document_number }}
                            </a>
                        </td>
                        <td style="color:var(--mcm-muted);font-size:.78rem;">{{ $doc->document_type ?? '—' }}</td>
                        <td style="color:var(--mcm-muted);font-size:.78rem;">{{ $doc->due_date?->format('d/m/Y') ?? '—' }}</td>
                        <td style="text-align:right;white-space:nowrap;">
                            <div style="display:flex;align-items:center;gap:.45rem;justify-content:flex-end;">
                                <div class="cv-mora-bar">
                                    <div class="cv-mora-fill" style="width:{{ $moraPct }}%;background:{{ $moraColor }};"></div>
                                </div>
                                @if($liveDays > 0)
                                    <span class="badge-pill {{ $liveDays > 90 ? 'badge-red' : ($liveDays > 30 ? 'badge-amber' : 'badge-green') }}">{{ $liveDays }}d</span>
                                @else
                                    <span class="badge-pill badge-gray">Al día</span>
                                @endif
                            </div>
                        </td>
                        <td style="text-align:right;font-family:ui-monospace,'Courier New',monospace;font-size:.78rem;font-weight:600;color:var(--mcm-text-strong);">
                            ${{ number_format($doc->original_amount, 0, ',', '.') }}
                        </td>
                        <td style="text-align:right;font-family:ui-monospace,'Courier New',monospace;font-size:.78rem;font-weight:700;color:{{ $doc->pending_amount > 0 ? 'var(--mcm-text-strong)' : 'var(--mcm-green)' }};">
                            ${{ number_format($doc->pending_amount, 0, ',', '.') }}
                        </td>
                        <td><span class="badge-pill {{ $rb['pill'] }}">{{ $rb['label'] }}</span></td>
                        <td>
                            @php $s = $doc->status; @endphp
                            <span class="badge-pill {{ $s === 'active' ? 'badge-green' : ($s === 'partial' ? 'badge-amber' : 'badge-gray') }}">
                                {{ match($s) { 'active'=>'Activo','partial'=>'Parcial','in_process'=>'En proceso','closed'=>'Cerrado',default=>$s??'—' } }}
                            </span>
                        </td>
                        <td style="text-align:center;">
                            <button type="button"
                                    wire:click="openManagementModal({{ $doc->id }})"
                                    class="btn-ghost"
                                    style="font-size:.72rem;padding:.28rem .55rem;">
                                Gestionar
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9">
                            <div class="cv-empty">
                                <x-heroicon-o-document-text />
                                @if(trim($this->docSearch) !== '')
                                    <p>No hay documentos que coincidan con «{{ trim($this->docSearch) }}».</p>
                                    <button type="button" wire:click="clearDocSearch" class="btn-ghost" style="margin-top:.5rem;font-size:.75rem;">
                                        Quitar búsqueda
                                    </button>
                                @else
                                    <p>Sin documentos de cartera registrados para este cliente.</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($ps['total_balance'] > 0)
                <tfoot>
                    <tr>
                        <td colspan="4" style="font-size:.76rem;color:var(--mcm-muted);font-weight:700;">TOTAL</td>
                        <td style="text-align:right;font-family:ui-monospace,'Courier New',monospace;font-size:.8rem;font-weight:700;color:var(--mcm-text-strong);">
                            —
                        </td>
                        <td style="text-align:right;font-family:ui-monospace,'Courier New',monospace;font-size:.82rem;font-weight:780;color:var(--mcm-text-strong);">
                            ${{ number_format($ps['total_balance'], 0, ',', '.') }}
                        </td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
        @if($docs->hasPages())
        <div class="cv-docs-pagination" wire:key="docs-pagination-{{ $docs->currentPage() }}">
            {{ $docs->links('pagination::simple-tailwind') }}
        </div>
        @endif
    </div>

    {{-- ── Management Timeline ── --}}
    <div class="cv-card mcm-reveal">
        <div class="cv-card-head">
            <span class="cv-card-icon"><x-heroicon-o-clock /></span>
            <span class="cv-card-title">Historial de gestión</span>
            <span class="badge-pill badge-gray">{{ count($timeline) }} registros</span>
        </div>

        @if(count($timeline))
        <div class="cv-tl-list">
            @foreach($timeline as $entry)
            @php
                $ts = $typeColors[$entry['type_key']] ?? $defaultTypeStyle;
                $ti = $typeIcons[$entry['type_key']]  ?? $defaultIcon;
            @endphp
            <div class="cv-tl-item">
                <div class="cv-tl-type-dot" style="background:{{ $ts['bg'] }};color:{{ $ts['ico'] }};">
                    <x-dynamic-component :component="$ti" />
                </div>
                <div style="min-width:0;">
                    <div class="cv-tl-row">
                        <span class="cv-tl-type">{{ $entry['type'] }}
                            @if($entry['doc_number'])
                            <span style="font-weight:400;color:var(--mcm-muted);font-size:.74rem;">
                                &nbsp;·&nbsp;{{ $entry['doc_number'] }}
                            </span>
                            @endif
                        </span>
                        <span class="cv-tl-date">{{ $entry['date'] }}</span>
                    </div>
                    @if($entry['subject'])
                    <p class="cv-tl-subj">{{ $entry['subject'] }}</p>
                    @endif
                    @if($entry['description'])
                    <p class="cv-tl-desc">{{ \Illuminate\Support\Str::limit($entry['description'], 220) }}</p>
                    @endif
                    <p class="cv-tl-meta">
                        @if($entry['result']){{ $resultLabels[$entry['result']] ?? $entry['result'] }}@endif
                        @if($entry['advisor']) &nbsp;·&nbsp; Asesor: {{ $entry['advisor'] }} @endif
                        @if($entry['promised']) &nbsp;·&nbsp; <span style="color:var(--mcm-amber);font-weight:700;">Promesa {{ $entry['promised'] }}</span> ({{ $entry['promised_date'] }}) @endif
                    </p>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="cv-empty">
            <x-heroicon-o-clock />
            <p>Sin gestiones registradas para este cliente.</p>
        </div>
        @endif
    </div>

    @if($showMgmtModal)
    @include('filament.resources.client-resource.partials.management-modal')
    @endif

</div>
</x-filament-panels::page>
