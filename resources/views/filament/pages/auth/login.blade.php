@php
    $heading = $this->getHeading();
    $subheading = $this->getSubheading();
    $hasLogo = $this->hasLogo();
@endphp

<div class="mcm-auth-root">
    @include('filament.partials.mcm-login-styles')

    <div class="mcm-auth">
        <section class="mcm-auth-panel">
            <div class="mcm-auth-card-top">
                @include('filament.partials.mcm-brand-logo')
            </div>

            <div class="fi-simple-page mcm-auth-card">
                <div class="fi-simple-page-content">
                    @if (filled($heading) || $hasLogo || filled($subheading))
                        <x-filament-panels::header.simple
                            :heading="$heading"
                            :logo="false"
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
