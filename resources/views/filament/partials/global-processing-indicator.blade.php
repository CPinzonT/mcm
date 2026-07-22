<style>
    #mcm-global-processing {
        position: fixed;
        inset: 0;
        z-index: 99999;
        display: grid;
        place-items: center;
        background: rgba(15, 23, 42, .22);
        backdrop-filter: blur(1.5px);
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity .16s ease, visibility .16s ease;
    }

    #mcm-global-processing.is-visible {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }

    .mcm-global-processing-card {
        display: flex;
        align-items: center;
        gap: .8rem;
        min-width: 12rem;
        padding: .85rem 1.05rem;
        border: 1px solid rgba(148, 163, 184, .35);
        border-radius: .8rem;
        background: rgba(255, 255, 255, .96);
        color: #0f172a;
        box-shadow: 0 18px 45px rgba(15, 23, 42, .25);
        font-size: .84rem;
        font-weight: 700;
    }

    .dark .mcm-global-processing-card {
        border-color: rgba(148, 163, 184, .22);
        background: rgba(30, 41, 59, .97);
        color: #f8fafc;
    }

    .mcm-global-processing-spinner {
        width: 1.45rem;
        height: 1.45rem;
        flex: 0 0 auto;
        border: 3px solid rgba(40, 82, 160, .22);
        border-top-color: #2852a0;
        border-radius: 999px;
        animation: mcm-global-spin .7s linear infinite;
    }

    .mcm-global-processing-bar {
        position: absolute;
        inset: 0 0 auto;
        height: 3px;
        overflow: hidden;
        background: rgba(255, 255, 255, .28);
    }

    .mcm-global-processing-bar::after {
        content: '';
        display: block;
        width: 35%;
        height: 100%;
        background: #60a5fa;
        animation: mcm-global-progress 1s ease-in-out infinite;
    }

    @keyframes mcm-global-spin {
        to { transform: rotate(360deg); }
    }

    @keyframes mcm-global-progress {
        from { transform: translateX(-110%); }
        to { transform: translateX(310%); }
    }

    @media (prefers-reduced-motion: reduce) {
        .mcm-global-processing-spinner,
        .mcm-global-processing-bar::after {
            animation-duration: 1.8s;
        }
    }
</style>

<div
    id="mcm-global-processing"
    role="status"
    aria-live="polite"
    aria-hidden="true"
>
    <div class="mcm-global-processing-bar" aria-hidden="true"></div>
    <div class="mcm-global-processing-card">
        <span class="mcm-global-processing-spinner" aria-hidden="true"></span>
        <span data-processing-message>Procesando…</span>
    </div>
</div>

<script>
    (() => {
        if (window.__mcmProcessingIndicatorInstalled) return;
        window.__mcmProcessingIndicatorInstalled = true;

        const state = {
            pending: 0,
            timer: null,
            shownAt: 0,
        };

        const element = () => document.getElementById('mcm-global-processing');
        const messageElement = () => element()?.querySelector('[data-processing-message]');

        const show = (message = 'Procesando…') => {
            const overlay = element();
            if (!overlay) return;

            const label = messageElement();
            if (label) label.textContent = message;

            overlay.classList.add('is-visible');
            overlay.setAttribute('aria-hidden', 'false');
            state.shownAt = Date.now();
        };

        const hide = () => {
            const overlay = element();
            if (!overlay) return;

            overlay.classList.remove('is-visible');
            overlay.setAttribute('aria-hidden', 'true');
            state.shownAt = 0;
        };

        const begin = (message = 'Procesando…') => {
            state.pending += 1;
            if (state.pending !== 1) return;

            window.clearTimeout(state.timer);
            state.timer = window.setTimeout(() => show(message), 140);
        };

        const finish = () => {
            state.pending = Math.max(0, state.pending - 1);
            if (state.pending > 0) return;

            window.clearTimeout(state.timer);
            const remaining = state.shownAt ? Math.max(0, 260 - (Date.now() - state.shownAt)) : 0;
            state.timer = window.setTimeout(hide, remaining);
        };

        const reset = () => {
            state.pending = 0;
            window.clearTimeout(state.timer);
            hide();
        };

        window.McmProcessing = { begin, finish, reset };

        const registerLivewire = () => {
            if (!window.Livewire || window.__mcmProcessingLivewireHooked) return;
            window.__mcmProcessingLivewireHooked = true;

            try {
                window.Livewire.hook('request', ({ succeed, fail }) => {
                    begin();
                    succeed(() => finish());
                    fail(() => finish());
                });
            } catch (error) {
                console.warn('No se pudo registrar el indicador global de Livewire.', error);
            }
        };

        registerLivewire();
        document.addEventListener('livewire:init', registerLivewire, { once: true });

        document.addEventListener('livewire-upload-start', () => begin('Cargando archivo…'));
        document.addEventListener('livewire-upload-finish', finish);
        document.addEventListener('livewire-upload-error', finish);
        document.addEventListener('livewire-upload-cancel', finish);

        document.addEventListener('livewire:navigate', () => begin('Cargando…'));
        document.addEventListener('livewire:navigated', reset);

        document.addEventListener('submit', (event) => {
            window.setTimeout(() => {
                const form = event.target;
                const isLivewireForm = [...(form?.attributes ?? [])]
                    .some((attribute) => attribute.name.startsWith('wire:submit'));

                if (!event.defaultPrevented && !isLivewireForm && form?.checkValidity()) {
                    begin('Procesando formulario…');
                }
            });
        }, true);

        document.addEventListener('click', (event) => {
            if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }

            const link = event.target.closest('a[href]');
            const isLivewireNavigation = link
                ? [...link.attributes].some((attribute) => attribute.name.startsWith('wire:navigate'))
                : false;

            if (!link || link.target === '_blank' || link.hasAttribute('download') || isLivewireNavigation) {
                return;
            }

            const destination = new URL(link.href, window.location.href);
            if (destination.origin !== window.location.origin
                || (destination.pathname === window.location.pathname && destination.hash)) {
                return;
            }

            begin('Cargando…');
        });

        window.addEventListener('pageshow', reset);
    })();
</script>
