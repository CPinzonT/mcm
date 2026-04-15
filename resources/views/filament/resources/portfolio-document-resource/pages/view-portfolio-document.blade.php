<x-filament-panels::page>
@include('filament.pages.partials.modern-dashboard-styles')
@push('styles')
<style>
.vd-page {
    --vd-mora-normal:   var(--mcm-green);
    --vd-mora-low:      #22d3ee;
    --vd-mora-medium:   var(--mcm-amber);
    --vd-mora-high:     #f97316;
    --vd-mora-critical: var(--mcm-red);
}

/* ── Identity bar ─────────────────────────────────────── */
.vd-identity {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    padding: 1.25rem 1.5rem;
    background: var(--mcm-surface);
    border: 1px solid var(--mcm-border);
    border-left: 4px solid var(--mcm-accent);
    border-radius: 14px;
    box-shadow: var(--mcm-shadow-soft);
}
.vd-doc-icon {
    width: 52px; height: 52px;
    border-radius: 12px;
    background: color-mix(in srgb, var(--mcm-accent) 12%, transparent);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    color: var(--mcm-accent-strong);
}
.vd-doc-icon svg { width: 26px; height: 26px; }
.vd-identity-body { flex: 1; min-width: 0; }
.vd-doc-number { font-size: 1.2rem; font-weight: 700; color: var(--mcm-text); line-height: 1.2; }
.vd-doc-meta { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; margin-top: .35rem; }
.vd-doc-meta span { font-size: .78rem; color: var(--mcm-muted); }
.vd-doc-meta .sep { opacity: .35; }
.vd-identity-actions { display: flex; gap: .5rem; flex-shrink: 0; }

/* ── KPI strip ─────────────────────────────────────────── */
.vd-kpi-strip {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: .75rem;
}
@media (max-width: 900px) { .vd-kpi-strip { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 640px) { .vd-kpi-strip { grid-template-columns: repeat(2, 1fr); } }
.vd-kpi { background: var(--mcm-surface); border: 1px solid var(--mcm-border); border-radius: 12px; padding: .9rem 1rem; box-shadow: var(--mcm-shadow-soft); }
.vd-kpi-label { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: var(--mcm-muted); margin-bottom: .3rem; }
.vd-kpi-value { font-size: 1.25rem; font-weight: 700; color: var(--mcm-text); font-variant-numeric: tabular-nums; line-height: 1; }
.vd-kpi-value.danger { color: var(--mcm-red); }
.vd-kpi-value.amber  { color: var(--mcm-amber); }
.vd-kpi-value.green  { color: var(--mcm-green); }
.vd-kpi-sub { font-size: .72rem; color: var(--mcm-muted); margin-top: .25rem; }

/* ── Two-column main grid ──────────────────────────────── */
.vd-main-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}
@media (max-width: 860px) { .vd-main-grid { grid-template-columns: 1fr; } }

/* ── Cards ─────────────────────────────────────────────── */
.vd-card {
    background: var(--mcm-surface);
    border: 1px solid var(--mcm-border);
    border-radius: 14px;
    box-shadow: var(--mcm-shadow-soft);
}
.vd-card-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1rem 1.25rem .75rem;
    border-bottom: 1px solid var(--mcm-border);
}
.vd-card-title {
    font-size: .8rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .06em;
    color: var(--mcm-muted);
}
.vd-card-body { padding: 1rem 1.25rem; }

/* ── Data grid (label/value pairs) ────────────────────── */
.vd-data-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: .6rem 1rem;
}
.vd-field-label { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: var(--mcm-muted); margin-bottom: .1rem; }
.vd-field-value { font-size: .875rem; color: var(--mcm-text); font-weight: 500; }

/* ── Other docs mini-list ──────────────────────────────── */
.vd-mini-docs { display: flex; flex-direction: column; gap: .4rem; }
.vd-mini-doc {
    display: flex; align-items: center; justify-content: space-between;
    padding: .45rem .75rem;
    border-radius: 8px;
    background: var(--mcm-surface-soft);
    border: 1px solid var(--mcm-border);
    text-decoration: none;
}
.vd-mini-doc:hover { background: color-mix(in srgb, var(--mcm-accent) 6%, var(--mcm-surface-soft)); border-color: var(--mcm-accent); }
.vd-mini-doc-num { font-size: .78rem; font-weight: 600; color: var(--mcm-text); }
.vd-mini-doc-amt { font-size: .75rem; color: var(--mcm-muted); font-variant-numeric: tabular-nums; }

/* ── Progress bar (collected) ──────────────────────────── */
.vd-progress-track {
    height: 6px; border-radius: 99px;
    background: var(--mcm-surface-strong);
    margin-top: .5rem;
    overflow: hidden;
}
.vd-progress-fill { height: 100%; border-radius: 99px; background: var(--mcm-green); }

/* ── Management form ───────────────────────────────────── */
.vd-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: .75rem;
}
@media (max-width: 640px) { .vd-form-grid { grid-template-columns: 1fr; } }
.vd-label {
    display: block;
    font-size: .72rem; font-weight: 600;
    text-transform: uppercase; letter-spacing: .04em;
    color: var(--mcm-muted); margin-bottom: .3rem;
}
.vd-input {
    width: 100%; padding: .55rem .75rem;
    background: var(--mcm-surface-soft);
    border: 1px solid var(--mcm-border);
    border-radius: 8px;
    color: var(--mcm-text);
    font-size: .875rem;
    outline: none;
    appearance: none;
    -webkit-appearance: none;
}
.vd-input:focus { border-color: var(--mcm-accent); box-shadow: 0 0 0 3px color-mix(in srgb, var(--mcm-accent) 15%, transparent); }
.vd-textarea { min-height: 100px; resize: vertical; font-family: inherit; line-height: 1.5; }
.vd-select { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right .65rem center; padding-right: 2rem; cursor: pointer; }
.vd-type-tabs { display: flex; flex-wrap: wrap; gap: .4rem; }
.vd-type-tab {
    padding: .4rem .85rem;
    border-radius: 99px;
    font-size: .78rem; font-weight: 600;
    border: 1.5px solid var(--mcm-border);
    background: var(--mcm-surface-soft);
    color: var(--mcm-muted);
    cursor: pointer;
}
.vd-type-tab:hover { border-color: var(--mcm-accent); color: var(--mcm-accent); }
.vd-type-tab.active { background: var(--mcm-accent); border-color: var(--mcm-accent); color: #fff; }
.vd-quick-replies { display: flex; flex-wrap: wrap; gap: .4rem; margin-bottom: .6rem; }
.vd-qr-btn {
    padding: .3rem .7rem;
    border-radius: 6px;
    font-size: .73rem;
    border: 1px solid var(--mcm-border);
    background: var(--mcm-surface-soft);
    color: var(--mcm-text);
    cursor: pointer;
}
.vd-qr-btn:hover { border-color: var(--mcm-accent); color: var(--mcm-accent-strong); background: color-mix(in srgb, var(--mcm-accent) 6%, var(--mcm-surface)); }

/* ── Promise panel ─────────────────────────────────────── */
.vd-promise-panel {
    padding: .9rem 1rem;
    border-radius: 10px;
    border: 1px solid color-mix(in srgb, var(--mcm-amber) 35%, var(--mcm-border));
    background: color-mix(in srgb, var(--mcm-amber) 5%, var(--mcm-surface));
}
.vd-promise-title { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--mcm-amber); margin-bottom: .6rem; }

/* ── Timeline ──────────────────────────────────────────── */
.vd-timeline { display: flex; flex-direction: column; gap: 0; padding: 0 1.25rem 1rem; }
.vd-tl-item { display: flex; gap: .9rem; padding: .9rem 0; border-bottom: 1px solid var(--mcm-border); }
.vd-tl-item:last-child { border-bottom: none; }
.vd-tl-dot {
    width: 36px; height: 36px; flex-shrink: 0;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
}
.vd-tl-dot svg { width: 18px; height: 18px; }
.vd-tl-body { flex: 1; min-width: 0; }
.vd-tl-meta { font-size: .72rem; color: var(--mcm-muted); margin-bottom: .25rem; display: flex; flex-wrap: wrap; gap: .4rem; align-items: center; }
.vd-tl-subject { font-size: .875rem; font-weight: 600; color: var(--mcm-text); margin-bottom: .2rem; }
.vd-tl-desc { font-size: .82rem; color: var(--mcm-muted); white-space: pre-wrap; line-height: 1.5; }
.vd-tl-promise {
    display: inline-flex; align-items: center; gap: .35rem;
    margin-top: .4rem;
    padding: .25rem .6rem;
    border-radius: 6px;
    font-size: .73rem; font-weight: 600;
    background: color-mix(in srgb, var(--mcm-amber) 10%, transparent);
    border: 1px solid color-mix(in srgb, var(--mcm-amber) 30%, transparent);
    color: var(--mcm-amber);
}

/* ── Empty timeline ────────────────────────────────────── */
.vd-empty {
    text-align: center; padding: 2.5rem 1rem;
    color: var(--mcm-muted); font-size: .875rem;
}
.vd-empty svg { width: 40px; height: 40px; margin: 0 auto .75rem; opacity: .3; display: block; }

/* ── Error messages ────────────────────────────────────── */
.vd-error { font-size: .73rem; color: var(--mcm-red); margin-top: .25rem; }
</style>
@endpush

@php
    $doc  = $this->record;
    $c    = $doc->client;
    $pct  = $doc->original_amount > 0
        ? min(100, round(((float)$doc->collected_amount / (float)$doc->original_amount) * 100))
        : 0;

    $riskColors = [
        'normal'   => 'green',
        'low'      => '',
        'medium'   => 'amber',
        'high'     => 'amber',
        'critical' => 'danger',
    ];
    $moraClass = $riskColors[$doc->risk_level] ?? '';

    $typeColors = [
        'call'      => ['bg' => 'color-mix(in srgb,var(--mcm-accent) 12%,var(--mcm-surface))',      'ico' => 'var(--mcm-accent-strong)'],
        'email'     => ['bg' => 'color-mix(in srgb,#8B5CF6 10%,var(--mcm-surface))',                'ico' => '#8B5CF6'],
        'visit'     => ['bg' => 'color-mix(in srgb,var(--mcm-green) 12%,var(--mcm-surface))',       'ico' => 'var(--mcm-green)'],
        'agreement' => ['bg' => 'color-mix(in srgb,var(--mcm-amber) 12%,var(--mcm-surface))',       'ico' => 'var(--mcm-amber)'],
        'legal'     => ['bg' => 'color-mix(in srgb,var(--mcm-red) 12%,var(--mcm-surface))',         'ico' => 'var(--mcm-red)'],
        'other'     => ['bg' => 'color-mix(in srgb,var(--mcm-muted) 10%,var(--mcm-surface))',       'ico' => 'var(--mcm-muted)'],
    ];
    $typeIcons = [
        'call'      => 'heroicon-o-phone',
        'email'     => 'heroicon-o-envelope',
        'visit'     => 'heroicon-o-map-pin',
        'agreement' => 'heroicon-o-banknotes',
        'legal'     => 'heroicon-o-scale',
        'other'     => 'heroicon-o-ellipsis-horizontal-circle',
    ];

    $resultBadge = [
        'no_contact'      => ['label' => 'Sin Contacto',     'cls' => 'badge-gray'],
        'promise_to_pay'  => ['label' => 'Promesa de Pago',  'cls' => 'badge-green'],
        'partial_payment' => ['label' => 'Pago Parcial',     'cls' => 'badge-amber'],
        'refused'         => ['label' => 'Rechazó',          'cls' => 'badge-red'],
        'arrangement'     => ['label' => 'Acuerdo',          'cls' => 'badge-blue'],
        'other'           => ['label' => 'Otro',             'cls' => 'badge-gray'],
    ];

    $quickReplies = [
        'Cliente promete pagar esta semana',
        'Cliente solicita plazo adicional',
        'No contesta / buzón',
        'Número incorrecto',
        'Pago realizado',
        'Requiere revisión de saldo',
    ];

    $statusLabels = [
        'active'      => ['label' => 'Activo',      'cls' => 'badge-blue'],
        'partial'     => ['label' => 'Parcial',     'cls' => 'badge-amber'],
        'paid'        => ['label' => 'Pagado',      'cls' => 'badge-green'],
        'written_off' => ['label' => 'Castigado',   'cls' => 'badge-gray'],
        'in_process'  => ['label' => 'En Proceso',  'cls' => 'badge-blue'],
    ];
    $statusInfo = $statusLabels[$doc->status] ?? ['label' => $doc->status, 'cls' => 'badge-gray'];
@endphp

<div class="mcm-modern-page vd-page space-y-5"
     x-data="{
         mgType: @js($this->mgType),
         setType(t) { this.mgType = t; $wire.set('mgType', t); },
         appendReply(txt) { $wire.call('appendQuickReply', txt); },
     }">

    {{-- ── Identity bar ─────────────────────────────────── --}}
    <div class="vd-identity mcm-reveal">
        <div class="vd-doc-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
        </div>
        <div class="vd-identity-body">
            <div class="vd-doc-number">{{ $doc->document_type }} #{{ $doc->document_number }}</div>
            <div class="vd-doc-meta">
                <span class="badge-pill {{ $statusInfo['cls'] }}">{{ $statusInfo['label'] }}</span>
                <span class="sep">·</span>
                <span>Cliente: <strong>{{ $c?->name ?? '—' }}</strong></span>
                @if($c?->document_number)
                    <span class="sep">·</span>
                    <span>{{ $c->document_number }}</span>
                @endif
                @if($doc->advisor)
                    <span class="sep">·</span>
                    <span>Asesor: {{ $doc->advisor->name }}</span>
                @endif
                @if($doc->period_date)
                    <span class="sep">·</span>
                    <span>Corte: {{ $doc->period_date->format('M Y') }}</span>
                @endif
            </div>
        </div>
        <div class="vd-identity-actions">
            @if($c)
            <a href="{{ \App\Filament\Resources\ClientResource::getUrl('view', ['record' => $c->id]) }}"
               class="btn-ghost" style="font-size:.78rem;">
                Ver cliente
            </a>
            @endif
        </div>
    </div>

    {{-- ── KPI strip ─────────────────────────────────────── --}}
    <div class="vd-kpi-strip mcm-stagger">
        <div class="vd-kpi">
            <div class="vd-kpi-label">Saldo Original</div>
            <div class="vd-kpi-value">${{ number_format((float)$doc->original_amount, 0, ',', '.') }}</div>
            <div class="vd-kpi-sub">{{ $doc->currency ?? 'COP' }}</div>
        </div>
        <div class="vd-kpi">
            <div class="vd-kpi-label">Saldo Pendiente</div>
            <div class="vd-kpi-value {{ $doc->pending_amount > 0 ? 'danger' : 'green' }}">
                ${{ number_format((float)$doc->pending_amount, 0, ',', '.') }}
            </div>
            <div class="vd-kpi-sub">{{ $pct }}% recaudado</div>
        </div>
        <div class="vd-kpi">
            <div class="vd-kpi-label">Días de Mora</div>
            <div class="vd-kpi-value {{ $moraClass }}">{{ $doc->days_overdue }}</div>
            <div class="vd-kpi-sub">{{ $doc->risk_label }}</div>
        </div>
        <div class="vd-kpi">
            <div class="vd-kpi-label">Vencimiento</div>
            <div class="vd-kpi-value" style="font-size:1rem;">
                {{ $doc->due_date?->format('d/m/Y') ?? '—' }}
            </div>
            <div class="vd-kpi-sub">
                @if($doc->due_date)
                    @if($doc->due_date->isPast()) Vencido hace {{ $doc->due_date->diffForHumans() }}
                    @else Vence {{ $doc->due_date->diffForHumans() }}
                    @endif
                @endif
            </div>
        </div>
        <div class="vd-kpi">
            <div class="vd-kpi-label">Recaudado</div>
            <div class="vd-kpi-value green">${{ number_format((float)$doc->collected_amount, 0, ',', '.') }}</div>
            <div class="vd-progress-track">
                <div class="vd-progress-fill" style="width:{{ $pct }}%"></div>
            </div>
        </div>
    </div>

    {{-- ── Two-column: document detail + client/other docs ─ --}}
    <div class="vd-main-grid mcm-reveal">
        {{-- LEFT: document details --}}
        <div class="vd-card">
            <div class="vd-card-header">
                <span class="vd-card-title">Detalle del Documento</span>
            </div>
            <div class="vd-card-body">
                <div class="vd-data-grid">
                    <div>
                        <div class="vd-field-label">Número</div>
                        <div class="vd-field-value">{{ $doc->document_number }}</div>
                    </div>
                    <div>
                        <div class="vd-field-label">Tipo</div>
                        <div class="vd-field-value">{{ $doc->document_type }}</div>
                    </div>
                    @if($doc->account)
                    <div>
                        <div class="vd-field-label">Cuenta</div>
                        <div class="vd-field-value">{{ $doc->account }}</div>
                    </div>
                    @endif
                    @if($doc->logical_key)
                    <div>
                        <div class="vd-field-label">Clave Lógica</div>
                        <div class="vd-field-value">{{ $doc->logical_key }}</div>
                    </div>
                    @endif
                    <div>
                        <div class="vd-field-label">Fecha Emisión</div>
                        <div class="vd-field-value">{{ $doc->issue_date?->format('d/m/Y') ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="vd-field-label">Fecha Vencimiento</div>
                        <div class="vd-field-value">{{ $doc->due_date?->format('d/m/Y') ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="vd-field-label">Monto Original</div>
                        <div class="vd-field-value">${{ number_format((float)$doc->original_amount, 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <div class="vd-field-label">Saldo Pendiente</div>
                        <div class="vd-field-value">${{ number_format((float)$doc->pending_amount, 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <div class="vd-field-label">Recaudado</div>
                        <div class="vd-field-value">${{ number_format((float)$doc->collected_amount, 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <div class="vd-field-label">Días Mora</div>
                        <div class="vd-field-value {{ $moraClass }}">{{ $doc->days_overdue }} días</div>
                    </div>
                    <div>
                        <div class="vd-field-label">Nivel de Riesgo</div>
                        <div class="vd-field-value">
                            <span class="badge-pill {{ match($doc->risk_level) { 'normal' => 'badge-green', 'low' => 'badge-blue', 'medium' => 'badge-amber', 'high' => 'badge-amber', 'critical' => 'badge-red', default => 'badge-gray' } }}">
                                {{ $doc->risk_label }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="vd-field-label">Estado</div>
                        <div class="vd-field-value">
                            <span class="badge-pill {{ $statusInfo['cls'] }}">{{ $statusInfo['label'] }}</span>
                        </div>
                    </div>
                </div>
                @if($doc->notes)
                <div style="margin-top:1rem; padding-top:.75rem; border-top:1px solid var(--mcm-border);">
                    <div class="vd-field-label">Notas</div>
                    <div class="vd-field-value" style="font-size:.82rem; line-height:1.5; color:var(--mcm-muted); white-space:pre-wrap;">{{ $doc->notes }}</div>
                </div>
                @endif
            </div>
        </div>

        {{-- RIGHT: client info + other docs --}}
        <div class="space-y-4">
            {{-- Client summary --}}
            @if($c)
            <div class="vd-card">
                <div class="vd-card-header">
                    <span class="vd-card-title">Cliente</span>
                    <a href="{{ \App\Filament\Resources\ClientResource::getUrl('view', ['record' => $c->id]) }}"
                       style="font-size:.73rem; color:var(--mcm-accent); text-decoration:none; font-weight:600;">
                        Ver perfil completo →
                    </a>
                </div>
                <div class="vd-card-body">
                    <div class="vd-data-grid">
                        <div style="grid-column:1/-1;">
                            <div class="vd-field-label">Razón Social</div>
                            <div class="vd-field-value" style="font-size:1rem; font-weight:700;">{{ $c->name }}</div>
                        </div>
                        @if($c->document_number)
                        <div>
                            <div class="vd-field-label">{{ $c->document_type ?? 'NIT' }}</div>
                            <div class="vd-field-value">{{ $c->document_number }}</div>
                        </div>
                        @endif
                        @if($c->channel)
                        <div>
                            <div class="vd-field-label">Canal</div>
                            <div class="vd-field-value">{{ $c->channel }}</div>
                        </div>
                        @endif
                        @if($c->region)
                        <div>
                            <div class="vd-field-label">Regional</div>
                            <div class="vd-field-value">{{ $c->region }}</div>
                        </div>
                        @endif
                        @if($c->contact_name)
                        <div>
                            <div class="vd-field-label">Contacto</div>
                            <div class="vd-field-value">{{ $c->contact_name }}</div>
                        </div>
                        @endif
                        @if($c->contact_phone ?? $c->phone)
                        <div>
                            <div class="vd-field-label">Teléfono</div>
                            <div class="vd-field-value">{{ $c->contact_phone ?? $c->phone }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Other documents from same client --}}
            @php $otherDocs = $this->clientDocuments; @endphp
            @if(count($otherDocs))
            <div class="vd-card">
                <div class="vd-card-header">
                    <span class="vd-card-title">Otros Documentos del Cliente</span>
                    <span style="font-size:.73rem; color:var(--mcm-muted);">{{ count($otherDocs) }} doc(s)</span>
                </div>
                <div class="vd-card-body" style="padding:.75rem 1rem;">
                    <div class="vd-mini-docs">
                        @foreach($otherDocs as $od)
                        <a href="{{ \App\Filament\Resources\PortfolioDocumentResource::getUrl('view', ['record' => $od['id']]) }}" class="vd-mini-doc">
                            <div>
                                <div class="vd-mini-doc-num">{{ $od['type'] }} #{{ $od['number'] }}</div>
                                <div class="vd-mini-doc-amt">${{ $od['pending_amount'] }}</div>
                            </div>
                            <span class="badge-pill {{ match($od['risk_level']) { 'normal' => 'badge-green', 'low' => 'badge-blue', 'medium' => 'badge-amber', 'high' => 'badge-amber', 'critical' => 'badge-red', default => 'badge-gray' } }}" style="font-size:.67rem;">
                                {{ $od['days_overdue'] }}d
                            </span>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- ── Management log form ─────────────────────────────── --}}
    <div class="vd-card mcm-reveal" id="registro-gestion">
        <div class="vd-card-header">
            <span class="vd-card-title">Registrar Gestión</span>
        </div>
        <div class="vd-card-body space-y-4">

            {{-- Type selector --}}
            <div>
                <div class="vd-label">Tipo de Gestión</div>
                <div class="vd-type-tabs">
                    @foreach(['call' => 'Llamada', 'email' => 'Correo', 'visit' => 'Visita', 'agreement' => 'Acuerdo', 'legal' => 'Jurídico', 'other' => 'Otro'] as $tk => $tl)
                    <button type="button"
                            class="vd-type-tab"
                            :class="{ 'active': mgType === '{{ $tk }}' }"
                            @click="setType('{{ $tk }}')">
                        {{ $tl }}
                    </button>
                    @endforeach
                </div>
                @error('mgType') <div class="vd-error">{{ $message }}</div> @enderror
            </div>

            <div class="vd-form-grid">
                {{-- Subject --}}
                <div style="grid-column:1/-1;">
                    <label class="vd-label">Asunto <span style="color:var(--mcm-red)">*</span></label>
                    <input type="text" wire:model="mgSubject" class="vd-input" placeholder="Resumen breve de la gestión">
                    @error('mgSubject') <div class="vd-error">{{ $message }}</div> @enderror
                </div>

                {{-- Result --}}
                <div>
                    <label class="vd-label">Resultado</label>
                    <select wire:model="mgResult" class="vd-input vd-select">
                        <option value="">Sin resultado</option>
                        <option value="no_contact">Sin Contacto</option>
                        <option value="promise_to_pay">Promesa de Pago</option>
                        <option value="partial_payment">Pago Parcial</option>
                        <option value="refused">Rechazó</option>
                        <option value="arrangement">Acuerdo</option>
                        <option value="other">Otro</option>
                    </select>
                    @error('mgResult') <div class="vd-error">{{ $message }}</div> @enderror
                </div>

                {{-- Contact date --}}
                <div>
                    <label class="vd-label">Fecha de Contacto <span style="color:var(--mcm-red)">*</span></label>
                    <input type="date" wire:model="mgContactDate" class="vd-input">
                    @error('mgContactDate') <div class="vd-error">{{ $message }}</div> @enderror
                </div>

                {{-- Follow-up date --}}
                <div>
                    <label class="vd-label">Fecha de Seguimiento</label>
                    <input type="date" wire:model="mgFollowUp" class="vd-input">
                    @error('mgFollowUp') <div class="vd-error">{{ $message }}</div> @enderror
                </div>

                {{-- Empty --}}
                <div></div>

                {{-- Quick replies + description --}}
                <div style="grid-column:1/-1;">
                    <label class="vd-label">Observación <span style="color:var(--mcm-red)">*</span></label>
                    <div class="vd-quick-replies">
                        @foreach($quickReplies as $qr)
                        <button type="button" class="vd-qr-btn" @click="appendReply('{{ $qr }}')">{{ $qr }}</button>
                        @endforeach
                    </div>
                    <textarea wire:model="mgDescription" class="vd-input vd-textarea" placeholder="Describa el resultado de la gestión..." rows="4"></textarea>
                    @error('mgDescription') <div class="vd-error">{{ $message }}</div> @enderror
                </div>
            </div>

            {{-- Promise panel --}}
            <div class="vd-promise-panel">
                <div class="vd-promise-title">Acuerdo / Promesa de Pago</div>
                <div class="vd-form-grid" style="gap:.6rem;">
                    <div>
                        <label class="vd-label">Valor Prometido</label>
                        <input type="number" step="1000" min="0" wire:model="mgPromisedAmt" class="vd-input" placeholder="0">
                        @error('mgPromisedAmt') <div class="vd-error">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="vd-label">Fecha Prometida</label>
                        <input type="date" wire:model="mgPromisedDate" class="vd-input">
                        @error('mgPromisedDate') <div class="vd-error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div style="margin-top:.5rem; font-size:.72rem; color:var(--mcm-muted);">
                    Si registra fecha o valor se considerará automáticamente como promesa de pago.
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end;">
                <button type="button"
                        wire:click="saveManagement"
                        wire:loading.attr="disabled"
                        wire:target="saveManagement"
                        class="btn-primary">
                    <span wire:loading.remove wire:target="saveManagement">Guardar Gestión</span>
                    <span wire:loading wire:target="saveManagement">Guardando…</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ── Management timeline ─────────────────────────────── --}}
    <div class="vd-card mcm-reveal">
        <div class="vd-card-header">
            <span class="vd-card-title">Historial de Gestiones</span>
            <span style="font-size:.73rem; color:var(--mcm-muted);">{{ count($this->managementLogs) }} gestión(es)</span>
        </div>

        @php $logs = $this->managementLogs; @endphp

        @if(empty($logs))
            <div class="vd-empty">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                </svg>
                Sin gestiones registradas para este documento.
            </div>
        @else
        <div class="vd-timeline">
            @foreach($logs as $log)
            @php
                $tc  = $typeColors[$log['type']] ?? $typeColors['other'];
                $ti  = $typeIcons[$log['type']]  ?? 'heroicon-o-ellipsis-horizontal-circle';
                $rb  = $resultBadge[$log['result']] ?? null;
            @endphp
            <div class="vd-tl-item">
                <div class="vd-tl-dot" style="background:{{ $tc['bg'] }}; color:{{ $tc['ico'] }};">
                    <x-dynamic-component :component="$ti" />
                </div>
                <div class="vd-tl-body">
                    <div class="vd-tl-meta">
                        <span>{{ $log['date'] }}</span>
                        <span class="badge-pill badge-gray" style="font-size:.67rem;">{{ $log['type_label'] }}</span>
                        @if($rb)
                        <span class="badge-pill {{ $rb['cls'] }}" style="font-size:.67rem;">{{ $rb['label'] }}</span>
                        @endif
                        <span>{{ $log['advisor'] }}</span>
                        @if($log['follow_up'])
                        <span style="color:var(--mcm-amber);">↺ {{ $log['follow_up'] }}</span>
                        @endif
                    </div>
                    <div class="vd-tl-subject">{{ $log['subject'] }}</div>
                    @if($log['description'])
                    <div class="vd-tl-desc">{{ $log['description'] }}</div>
                    @endif
                    @if($log['promised_amt'] || $log['promised_date'])
                    <div class="vd-tl-promise">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width:13px;height:13px;">
                            <path d="M10.75 10.818v2.614A3.13 3.13 0 0011.888 13c.482-.315.612-.648.612-.875 0-.227-.13-.56-.612-.875a3.13 3.13 0 00-1.138-.432zM8.33 8.62c.053.055.115.11.176.162.1.073.203.139.308.197V6.604a2.415 2.415 0 00-.484.393c-.396.396-.396.934 0 1.623zm7.019 2.185c0 2.909-2.748 5.253-6.15 5.253S3.05 13.714 3.05 10.805C3.05 7.896 5.8 5.552 9.2 5.552s6.149 2.344 6.149 5.253z" />
                        </svg>
                        Promesa: ${{ $log['promised_amt'] }}
                        @if($log['promised_date']) — {{ $log['promised_date'] }} @endif
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>
</x-filament-panels::page>
