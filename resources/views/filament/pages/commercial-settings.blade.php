<x-filament-panels::page>

@include('filament.pages.partials.modern-dashboard-styles')

@push('styles')
<style>
.commercial-settings .commercial-hero-meta {
    display: grid;
    gap: 0.75rem;
    grid-template-columns: repeat(3, minmax(6.5rem, 1fr));
    min-width: min(100%, 29rem);
}

.commercial-settings .commercial-hero-stat {
    background: var(--mcm-surface-soft);
    border: 1px solid var(--mcm-border);
    border-radius: 8px;
    min-height: 4.25rem;
    padding: 0.8rem 0.9rem;
}

.commercial-settings .commercial-label,
.commercial-settings .commercial-hero-stat span {
    color: var(--mcm-muted);
    display: block;
    font-size: 0.78rem;
    font-weight: 740;
}

.commercial-settings .commercial-hero-stat strong {
    color: var(--mcm-text);
    display: block;
    font-size: 1.35rem;
    font-weight: 780;
    line-height: 1.1;
    margin-top: 0.28rem;
}

.commercial-settings .commercial-grid {
    display: grid;
    gap: 1rem;
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.commercial-settings .commercial-card,
.commercial-settings .commercial-info-card {
    background: var(--mcm-surface);
    border: 1px solid var(--mcm-border);
    border-radius: 8px;
    box-shadow: var(--mcm-shadow-soft);
    overflow: hidden;
}

.commercial-settings .commercial-card {
    display: grid;
    grid-template-rows: auto 1fr;
    min-height: 20rem;
}

.commercial-settings .commercial-card-head {
    align-items: flex-start;
    border-bottom: 1px solid var(--mcm-border);
    display: flex;
    gap: 0.85rem;
    justify-content: space-between;
    padding: 1.15rem 1.15rem 1rem;
}

.commercial-settings .commercial-icon {
    align-items: center;
    background: var(--mcm-accent-soft);
    border: 1px solid color-mix(in srgb, var(--mcm-accent) 30%, var(--mcm-border));
    border-radius: 8px;
    color: var(--mcm-accent-strong);
    display: inline-flex;
    height: 2.45rem;
    justify-content: center;
    width: 2.45rem;
}

.commercial-settings .commercial-icon svg {
    height: 1.15rem;
    width: 1.15rem;
}

.commercial-settings .commercial-title {
    color: var(--mcm-text);
    font-size: 1rem;
    font-weight: 760;
    line-height: 1.2;
}

.commercial-settings .commercial-copy {
    color: var(--mcm-muted);
    font-size: 0.82rem;
    margin-top: 0.25rem;
}

.commercial-settings .commercial-count {
    background: var(--mcm-surface-soft);
    border: 1px solid var(--mcm-border);
    border-radius: 8px;
    color: var(--mcm-text);
    font-size: 0.8rem;
    font-weight: 760;
    padding: 0.35rem 0.58rem;
    white-space: nowrap;
}

.commercial-settings .commercial-card-body {
    align-content: start;
    display: flex;
    flex-wrap: wrap;
    gap: 0.55rem;
    max-height: 17rem;
    overflow: auto;
    padding: 1rem 1.15rem 1.2rem;
}

.commercial-settings .commercial-pill {
    background: var(--mcm-surface-soft);
    border: 1px solid var(--mcm-border);
    border-radius: 8px;
    color: var(--mcm-text);
    display: inline-flex;
    font-size: 0.82rem;
    font-weight: 650;
    line-height: 1.1;
    max-width: 100%;
    padding: 0.48rem 0.68rem;
    overflow-wrap: anywhere;
}

.commercial-settings .commercial-empty {
    align-items: center;
    color: var(--mcm-muted);
    display: flex;
    font-size: 0.85rem;
    min-height: 8rem;
    width: 100%;
}

.commercial-settings .commercial-info-card {
    align-items: flex-start;
    display: grid;
    gap: 0.9rem;
    grid-template-columns: auto minmax(0, 1fr);
    padding: 1.2rem;
}

.commercial-settings .commercial-info-card .commercial-icon {
    background: color-mix(in srgb, var(--mcm-green) 14%, var(--mcm-surface));
    border-color: color-mix(in srgb, var(--mcm-green) 30%, var(--mcm-border));
    color: var(--mcm-green);
}

.commercial-settings .commercial-info-title {
    color: var(--mcm-text);
    font-size: 0.98rem;
    font-weight: 760;
}

.commercial-settings .commercial-info-copy {
    color: var(--mcm-muted);
    font-size: 0.86rem;
    line-height: 1.5;
    margin-top: 0.3rem;
}

@media (max-width: 1120px) {
    .commercial-settings .commercial-grid {
        grid-template-columns: 1fr;
    }

    .commercial-settings .commercial-card {
        min-height: auto;
    }
}

@media (max-width: 760px) {
    .commercial-settings .commercial-hero-meta,
    .commercial-settings .commercial-info-card {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@php
    $uens = $this->uens;
    $regionals = $this->regionals;
    $channels = $this->channels;
@endphp

<div class="mcm-modern-page commercial-settings space-y-5">
    <section class="page-hero">
        <div>
            <p class="commercial-label">Catalogos operativos</p>
            <h1>Parametrizacion Comercial</h1>
            <p>Dimensiones comerciales derivadas automaticamente de los clientes cargados.</p>
        </div>

        <div class="commercial-hero-meta">
            <div class="commercial-hero-stat">
                <span>UEN</span>
                <strong>{{ number_format(count($uens)) }}</strong>
            </div>
            <div class="commercial-hero-stat">
                <span>Regionales</span>
                <strong>{{ number_format(count($regionals)) }}</strong>
            </div>
            <div class="commercial-hero-stat">
                <span>Canales</span>
                <strong>{{ number_format(count($channels)) }}</strong>
            </div>
        </div>
    </section>

    <section class="commercial-grid">
        <article class="commercial-card">
            <header class="commercial-card-head">
                <div class="flex items-start gap-3">
                    <span class="commercial-icon"><x-heroicon-o-building-office /></span>
                    <div>
                        <div class="commercial-title">Unidades de Negocio</div>
                        <p class="commercial-copy">Agrupacion UEN encontrada en clientes.</p>
                    </div>
                </div>
                <span class="commercial-count">{{ number_format(count($uens)) }}</span>
            </header>
            <div class="commercial-card-body">
                @forelse($uens as $uen)
                    <span class="commercial-pill">{{ $uen }}</span>
                @empty
                    <div class="commercial-empty">Sin datos cargados.</div>
                @endforelse
            </div>
        </article>

        <article class="commercial-card">
            <header class="commercial-card-head">
                <div class="flex items-start gap-3">
                    <span class="commercial-icon"><x-heroicon-o-map-pin /></span>
                    <div>
                        <div class="commercial-title">Regionales</div>
                        <p class="commercial-copy">Zonas comerciales disponibles.</p>
                    </div>
                </div>
                <span class="commercial-count">{{ number_format(count($regionals)) }}</span>
            </header>
            <div class="commercial-card-body">
                @forelse($regionals as $regional)
                    <span class="commercial-pill">{{ $regional }}</span>
                @empty
                    <div class="commercial-empty">Sin datos cargados.</div>
                @endforelse
            </div>
        </article>

        <article class="commercial-card">
            <header class="commercial-card-head">
                <div class="flex items-start gap-3">
                    <span class="commercial-icon"><x-heroicon-o-signal /></span>
                    <div>
                        <div class="commercial-title">Canales</div>
                        <p class="commercial-copy">Canales comerciales detectados.</p>
                    </div>
                </div>
                <span class="commercial-count">{{ number_format(count($channels)) }}</span>
            </header>
            <div class="commercial-card-body">
                @forelse($channels as $channel)
                    <span class="commercial-pill">{{ $channel }}</span>
                @empty
                    <div class="commercial-empty">Sin datos cargados.</div>
                @endforelse
            </div>
        </article>
    </section>

    <section class="commercial-info-card">
        <span class="commercial-icon"><x-heroicon-o-arrow-path /></span>
        <div>
            <div class="commercial-info-title">Catalogos derivados automaticamente</div>
            <p class="commercial-info-copy">
                Los valores de UEN, Regional y Canal se extraen de los clientes creados desde las cargas de cartera.
                No requieren configuracion manual y se actualizan cuando se procesa un nuevo archivo.
            </p>
        </div>
    </section>
</div>

</x-filament-panels::page>
