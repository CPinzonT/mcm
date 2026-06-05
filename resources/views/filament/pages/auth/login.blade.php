@php
    $heading = $this->getHeading();
    $subheading = $this->getSubheading();
    $hasLogo = $this->hasLogo();
@endphp

<div class="mcm-auth-root">
    @include('filament.partials.mcm-login-styles')

    <div class="mcm-auth">
        <aside class="mcm-auth-brand" aria-hidden="true">
            <div class="mcm-auth-brand-inner">
                <div class="mcm-auth-mark">@include('filament.partials.mcm-brand-logo')</div>
                <h1 class="mcm-auth-title">Gestión de Cartera</h1>
                <p class="mcm-auth-lead">
                    Control operativo de cartera, recaudos y analítica comercial en un solo lugar.
                </p>
                <ul class="mcm-auth-features">
                    <li>Cargas y conciliación</li>
                    <li>Dashboard estratégico</li>
                    <li>Reportes y seguimiento</li>
                </ul>
            </div>
        </aside>

        <section class="mcm-auth-panel">
            <div class="fi-simple-page mcm-auth-card">
                <div class="fi-simple-page-content">
                    @if (filled($heading) || $hasLogo || filled($subheading))
                        <x-filament-panels::header.simple
                            :heading="$heading"
                            :logo="$hasLogo"
                            :subheading="$subheading"
                        />
                    @endif

                    {{ $this->content }}
                </div>

                <x-filament-actions::modals />
            </div>

            <p class="mcm-auth-footer">MCM Company SAS · Uso interno autorizado</p>
        </section>
    </div>
</div>
