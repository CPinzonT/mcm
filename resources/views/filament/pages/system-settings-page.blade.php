<x-filament-panels::page>
@include('filament.pages.partials.modern-dashboard-styles')

@push('styles')
<style>
/* ── System Settings ─────────────────────────────────────── */
.system-settings .ss-grid-2 {
    display: grid;
    gap: 1rem;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.system-settings .ss-full { grid-column: 1 / -1; }

/* Card base */
.system-settings .ss-card {
    background: var(--mcm-surface);
    border: 1px solid var(--mcm-border);
    border-radius: 12px;
    box-shadow: var(--mcm-shadow-soft);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.system-settings .ss-card-head {
    align-items: center;
    background: var(--mcm-surface-soft);
    border-bottom: 1px solid var(--mcm-border);
    display: flex;
    gap: .75rem;
    padding: .85rem 1.1rem;
}

.system-settings .ss-card-icon {
    align-items: center;
    background: var(--mcm-accent-soft);
    border: 1px solid color-mix(in srgb,var(--mcm-accent) 22%,var(--mcm-border));
    border-radius: 7px;
    color: var(--mcm-accent-strong);
    display: inline-flex;
    flex-shrink: 0;
    height: 1.95rem;
    justify-content: center;
    width: 1.95rem;
}
.system-settings .ss-card-icon svg { height:.88rem;width:.88rem; }
.system-settings .ss-card-icon.amber { background:color-mix(in srgb,var(--mcm-amber) 12%,var(--mcm-surface));border-color:color-mix(in srgb,var(--mcm-amber) 28%,var(--mcm-border));color:var(--mcm-amber); }
.system-settings .ss-card-icon.green { background:color-mix(in srgb,var(--mcm-green) 12%,var(--mcm-surface));border-color:color-mix(in srgb,var(--mcm-green) 28%,var(--mcm-border));color:var(--mcm-green); }
.system-settings .ss-card-icon.red   { background:color-mix(in srgb,var(--mcm-red) 12%,var(--mcm-surface));border-color:color-mix(in srgb,var(--mcm-red) 28%,var(--mcm-border));color:var(--mcm-red); }

.system-settings .ss-card-title { color:var(--mcm-text);font-size:.87rem;font-weight:760; }
.system-settings .ss-card-sub   { color:var(--mcm-muted);font-size:.73rem;margin-top:.08rem; }

.system-settings .ss-card-body { flex:1;padding:1.1rem; }

.system-settings .ss-card-foot {
    border-top: 1px solid var(--mcm-border);
    display: flex;
    justify-content: flex-end;
    padding: .72rem 1.1rem;
}

/* Fields */
.system-settings .ss-field { margin-bottom:.85rem; }
.system-settings .ss-field:last-child { margin-bottom:0; }

.system-settings .ss-field-grid-2 {
    display: grid;
    gap: .7rem;
    grid-template-columns: repeat(2, minmax(0,1fr));
}

.system-settings .ss-label {
    color: var(--mcm-muted);
    display: block;
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .045em;
    margin-bottom: .3rem;
    text-transform: uppercase;
}

.system-settings .ss-input {
    background: var(--mcm-surface-soft);
    border: 1px solid var(--mcm-border);
    border-radius: 7px;
    color: var(--mcm-text);
    font-size: .84rem;
    padding: .5rem .72rem;
    width: 100%;
}
.system-settings .ss-input:focus {
    border-color: var(--mcm-accent);
    box-shadow: 0 0 0 2.5px color-mix(in srgb,var(--mcm-accent) 14%,transparent);
    outline: none;
}
.system-settings .ss-input[type="select"],
.system-settings select.ss-input { cursor: pointer; }
.system-settings textarea.ss-input { min-height:5rem;resize:vertical; }

/* Logo zone */
.system-settings .ss-logo-zone {
    align-items: center;
    background: var(--mcm-surface-soft);
    border: 2px dashed var(--mcm-border);
    border-radius: 9px;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    gap: .55rem;
    justify-content: center;
    min-height: 7.5rem;
    padding: 1rem;
    position: relative;
    text-align: center;
    width: 100%;
}
.system-settings .ss-logo-zone:hover {
    background: color-mix(in srgb,var(--mcm-accent-soft) 40%,var(--mcm-surface-soft));
    border-color: var(--mcm-accent);
}
.system-settings .ss-logo-zone input[type="file"] {
    cursor: pointer;
    height: 100%;
    left: 0;
    opacity: 0;
    position: absolute;
    top: 0;
    width: 100%;
}
.system-settings .ss-logo-img {
    border-radius: 6px;
    max-height: 5.5rem;
    max-width: 100%;
    object-fit: contain;
}
.system-settings .ss-logo-zone-title {
    color: var(--mcm-accent);
    font-size: .82rem;
    font-weight: 740;
}
.system-settings .ss-logo-zone-hint {
    color: var(--mcm-muted);
    font-size: .75rem;
}

/* ── Color pickers ── */
.system-settings .ss-color-field { display:flex;gap:.6rem;align-items:center; }

.system-settings .ss-swatch {
    border: 2px solid var(--mcm-border);
    border-radius: 8px;
    cursor: pointer;
    flex-shrink: 0;
    height: 2.25rem;
    overflow: hidden;
    padding: 0;
    width: 2.75rem;
}
.system-settings .ss-swatch:hover { border-color: var(--mcm-accent); }
.system-settings .ss-swatch input[type="color"] {
    border: none;
    cursor: pointer;
    height: 200%;
    margin: -25%;
    padding: 0;
    width: 200%;
}
.system-settings .ss-hex {
    background: var(--mcm-surface-soft);
    border: 1px solid var(--mcm-border);
    border-radius: 7px;
    color: var(--mcm-text);
    flex: 1;
    font-family: 'Courier New', monospace;
    font-size: .8rem;
    padding: .48rem .68rem;
}
.system-settings .ss-hex:focus { border-color:var(--mcm-accent);outline:none; }
.system-settings .ss-color-name {
    color: var(--mcm-muted);
    font-size: .74rem;
    font-weight: 700;
    min-width: 5.5rem;
    text-transform: uppercase;
    letter-spacing: .04em;
}

/* ── Theme cards ── */
.system-settings .ss-themes {
    display: flex;
    gap: .55rem;
}
.system-settings .ss-theme-card {
    align-items: center;
    background: var(--mcm-surface-soft);
    border: 1.5px solid var(--mcm-border);
    border-radius: 9px;
    cursor: pointer;
    display: flex;
    flex: 1;
    flex-direction: column;
    gap: .38rem;
    padding: .72rem .5rem;
    text-align: center;
}
.system-settings .ss-theme-card:hover {
    border-color: color-mix(in srgb,var(--mcm-accent) 38%,var(--mcm-border));
}
.system-settings .ss-theme-card.active {
    background: var(--mcm-accent-soft);
    border-color: var(--mcm-accent);
}
.system-settings .ss-theme-card svg { height:1.35rem;width:1.35rem;color:var(--mcm-muted); }
.system-settings .ss-theme-card.active svg { color:var(--mcm-accent-strong); }
.system-settings .ss-theme-lbl { color:var(--mcm-muted);font-size:.74rem;font-weight:700; }
.system-settings .ss-theme-card.active .ss-theme-lbl { color:var(--mcm-accent-strong); }

/* ── Live preview card ── */
.system-settings .ss-preview-wrap {
    border: 1px solid var(--mcm-border);
    border-radius: 10px;
    margin-top: 1.1rem;
    overflow: hidden;
}
.system-settings .ss-preview-titlebar {
    align-items: center;
    border-bottom: 1px solid;
    display: flex;
    gap: .4rem;
    padding: .52rem .8rem;
}
.system-settings .ss-preview-dot {
    border-radius: 50%;
    height: .52rem;
    width: .52rem;
}
.system-settings .ss-preview-label { font-size:.72rem;font-weight:700;opacity:.65; }
.system-settings .ss-preview-body { padding: .85rem; }
.system-settings .ss-preview-heading { font-size:.88rem;font-weight:760;margin-bottom:.28rem; }
.system-settings .ss-preview-text { font-size:.73rem;line-height:1.5;margin-bottom:.65rem;opacity:.65; }
.system-settings .ss-preview-actions { align-items:center;display:flex;gap:.45rem; }
.system-settings .ss-preview-btn {
    border: none;
    border-radius: 6px;
    cursor: default;
    font-size: .73rem;
    font-weight: 700;
    padding: .38rem .78rem;
}
.system-settings .ss-preview-badge {
    border-radius: 999px;
    font-size: .68rem;
    font-weight: 700;
    padding: .2rem .55rem;
}

/* ── Risk track ── */
.system-settings .ss-risk-bar {
    border-radius: 8px;
    display: flex;
    gap: 2px;
    height: 1.2rem;
    margin-bottom: .85rem;
    overflow: hidden;
}
.system-settings .ss-risk-seg { border-radius:4px;min-width:1.5rem; }
.system-settings .ss-risk-legend { display:flex;flex-wrap:wrap;gap:.45rem .7rem; }
.system-settings .ss-risk-leg { align-items:center;display:flex;gap:.38rem; }
.system-settings .ss-risk-dot { border-radius:50%;flex-shrink:0;height:.58rem;width:.58rem; }
.system-settings .ss-risk-leg-txt { color:var(--mcm-muted);font-size:.73rem; }
.system-settings .ss-risk-leg-txt strong { color:var(--mcm-text);font-weight:700; }

/* ── Sticky save bar ── */
.system-settings .ss-save-sticky {
    align-items: center;
    background: var(--mcm-surface);
    border: 1px solid var(--mcm-border);
    border-radius: 10px;
    box-shadow: 0 4px 18px rgba(0,0,0,.09), 0 1px 4px rgba(0,0,0,.06);
    display: flex;
    gap: 1rem;
    justify-content: space-between;
    padding: .9rem 1.2rem;
    position: sticky;
    bottom: 1rem;
    z-index: 20;
}
.system-settings .ss-save-hint { color:var(--mcm-muted);font-size:.76rem;line-height:1.45;max-width:30rem; }

.system-settings .ss-btn-state {
    align-items: center;
    display: inline-flex;
    gap: .45rem;
}


@media (max-width: 960px) {
    .system-settings .ss-grid-2 { grid-template-columns: 1fr; }
    .system-settings .ss-field-grid-2 { grid-template-columns: 1fr; }
    .system-settings .ss-themes { flex-direction: column; }
}
</style>
@endpush

@php
    $riskColors = [
        'normal'   => '#6ee7b7',
        'low'      => '#93c5fd',
        'medium'   => '#fcd34d',
        'high'     => '#fb923c',
        'critical' => '#f87171',
    ];
    $sortedRisks = collect($this->riskLevels)->sortBy('order');
    $maxDays     = $sortedRisks->max(fn($r) => $r->days_max ?? 365) ?: 365;
@endphp

<div class="mcm-modern-page system-settings space-y-5"
     x-data="{
         primaryColor:   @js($this->settings['primary_color']   ?? '#2563eb'),
         accentColor:    @js($this->settings['accent_color']    ?? '#3b82f6'),
         secondaryColor: @js($this->settings['secondary_color'] ?? '#64748b'),
         previewTheme:   'light',

         syncHex(prop, val) {
             if (/^#[0-9a-fA-F]{6}$/.test(val)) this[prop] = val;
         },

         previewBg()   { return this.previewTheme === 'dark' ? '#1e293b' : '#f8fafc'; },
         previewText() { return this.previewTheme === 'dark' ? '#f1f5f9' : '#1e293b'; },
         previewSub()  { return this.previewTheme === 'dark' ? '#94a3b8' : '#64748b'; },
         previewBorder(){ return this.previewTheme === 'dark' ? '#334155' : '#e2e8f0'; },

         saveAll() {
             $wire.set('settings.primary_color',   this.primaryColor);
             $wire.set('settings.accent_color',    this.accentColor);
             $wire.set('settings.secondary_color', this.secondaryColor);
             $wire.call('saveSettings');
         }
     }">

    {{-- Hero --}}
    <section class="page-hero">
        <div>
            <p class="dash-section-title" style="margin-bottom:.3rem;">Configuración</p>
            <h1>General del Sistema</h1>
            <p>Identidad corporativa, paleta de marca y personalización visual del sistema.</p>
        </div>
    </section>

    {{-- ─── Sección 1: Identidad corporativa ─── --}}
    <div class="ss-card">
        <div class="ss-card-head">
            <span class="ss-card-icon"><x-heroicon-o-building-office-2 /></span>
            <div>
                <div class="ss-card-title">Identidad corporativa</div>
                <div class="ss-card-sub">Datos de la empresa reflejados en reportes y encabezados.</div>
            </div>
        </div>
        <div class="ss-card-body">
            <div class="ss-grid-2" style="gap:1.1rem;">

                {{-- Logo --}}
                <div class="ss-field">
                    <label class="ss-label">Logotipo</label>
                    <div class="ss-logo-zone" title="Haz clic para subir un logotipo">
                        <input type="file" wire:model="logoFile" accept="image/png,image/jpeg,image/svg+xml,image/webp"/>
                        @if($this->logoPreviewUrl)
                            <img src="{{ $this->logoPreviewUrl }}"
                                 alt="Logo actual" class="ss-logo-img"/>
                            <span class="ss-logo-zone-title">Cambiar logotipo</span>
                            <span class="ss-logo-zone-hint">Haz clic para reemplazar la imagen</span>
                        @else
                            <x-heroicon-o-photo style="width:2rem;height:2rem;color:var(--mcm-muted);opacity:.45;"/>
                            <span class="ss-logo-zone-title">Subir logotipo</span>
                            <span class="ss-logo-zone-hint">PNG, SVG, WEBP — máx. 2 MB</span>
                        @endif
                    </div>
                    <div wire:loading wire:target="logoFile"
                         style="color:var(--mcm-muted);font-size:.74rem;margin-top:.35rem;">
                        Subiendo…
                    </div>
                </div>

                {{-- Nombre --}}
                <div>
                    <div class="ss-field">
                        <label class="ss-label">Nombre de la empresa</label>
                        <input type="text" wire:model="settings.company_name" class="ss-input"
                               placeholder="Nombre corporativo"/>
                    </div>
                    <div class="ss-field">
                        <label class="ss-label">Sitio web</label>
                        <input type="url" wire:model="settings.website" class="ss-input"
                               placeholder="https://empresa.com"/>
                    </div>
                </div>

                {{-- Contacto --}}
                <div class="ss-field-grid-2 ss-full">
                    <div class="ss-field">
                        <label class="ss-label">Correo</label>
                        <input type="email" wire:model="settings.email" class="ss-input"
                               placeholder="info@empresa.com"/>
                    </div>
                    <div class="ss-field">
                        <label class="ss-label">Teléfono</label>
                        <input type="text" wire:model="settings.phone" class="ss-input"
                               placeholder="+57 1 000 0000"/>
                    </div>
                    <div class="ss-field ss-full">
                        <label class="ss-label">Dirección</label>
                        <input type="text" wire:model="settings.address" class="ss-input"
                               placeholder="Calle 000 # 00-00, Ciudad"/>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ─── Sección 2: Personalización de marca ─── --}}
    <div class="ss-card">
        <div class="ss-card-head">
            <span class="ss-card-icon amber"><x-heroicon-o-swatch /></span>
            <div>
                <div class="ss-card-title">Personalización de marca</div>
                <div class="ss-card-sub">Colores y tema visual. La vista previa se actualiza en tiempo real.</div>
            </div>
        </div>
        <div class="ss-card-body">
            <div class="ss-grid-2" style="gap:1.5rem;">

                {{-- Color pickers --}}
                <div>
                    <div class="ss-field">
                        <label class="ss-label">Color primario</label>
                        <div class="ss-color-field">
                            <label class="ss-swatch">
                                <input type="color"
                                       :value="primaryColor"
                                       @input="primaryColor = $event.target.value"/>
                            </label>
                            <input type="text" class="ss-hex"
                                   :value="primaryColor"
                                   @input.debounce.300ms="syncHex('primaryColor', $event.target.value)"
                                   placeholder="#2563eb" maxlength="7"/>
                            <span class="ss-color-name">Primario</span>
                        </div>
                    </div>
                    <div class="ss-field">
                        <label class="ss-label">Color de acento</label>
                        <div class="ss-color-field">
                            <label class="ss-swatch">
                                <input type="color"
                                       :value="accentColor"
                                       @input="accentColor = $event.target.value"/>
                            </label>
                            <input type="text" class="ss-hex"
                                   :value="accentColor"
                                   @input.debounce.300ms="syncHex('accentColor', $event.target.value)"
                                   placeholder="#3b82f6" maxlength="7"/>
                            <span class="ss-color-name">Acento</span>
                        </div>
                    </div>
                    <div class="ss-field">
                        <label class="ss-label">Color secundario</label>
                        <div class="ss-color-field">
                            <label class="ss-swatch">
                                <input type="color"
                                       :value="secondaryColor"
                                       @input="secondaryColor = $event.target.value"/>
                            </label>
                            <input type="text" class="ss-hex"
                                   :value="secondaryColor"
                                   @input.debounce.300ms="syncHex('secondaryColor', $event.target.value)"
                                   placeholder="#64748b" maxlength="7"/>
                            <span class="ss-color-name">Secundario</span>
                        </div>
                    </div>

                    <div class="ss-field" style="margin-top:1.1rem;">
                        <label class="ss-label">Tema de previsualización</label>
                        <div class="ss-themes">
                            <div class="ss-theme-card" :class="previewTheme === 'light' ? 'active' : ''"
                                 @click="previewTheme = 'light'">
                                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/>
                                </svg>
                                <span class="ss-theme-lbl">Claro</span>
                            </div>
                            <div class="ss-theme-card" :class="previewTheme === 'dark' ? 'active' : ''"
                                 @click="previewTheme = 'dark'">
                                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"/>
                                </svg>
                                <span class="ss-theme-lbl">Oscuro</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Live preview --}}
                <div>
                    <label class="ss-label" style="margin-bottom:.55rem;">Vista previa en vivo</label>
                    <div class="ss-preview-wrap" :style="'border-color:' + previewBorder()">
                        <div class="ss-preview-titlebar"
                             :style="'background:' + previewBg() + ';border-color:' + previewBorder()">
                            <div class="ss-preview-dot" :style="'background:' + primaryColor"></div>
                            <div class="ss-preview-dot" :style="'background:' + accentColor"></div>
                            <div class="ss-preview-dot" :style="'background:' + secondaryColor"></div>
                            <span class="ss-preview-label" :style="'color:' + previewText()">
                                {{ $this->settings['company_name'] ?? 'Mi Empresa' }}
                            </span>
                        </div>
                        <div class="ss-preview-body" :style="'background:' + previewBg()">
                            <div class="ss-preview-heading" :style="'color:' + previewText()">
                                Resumen de cartera
                            </div>
                            <div class="ss-preview-text" :style="'color:' + previewSub()">
                                Panel de gestión y seguimiento de obligaciones.
                            </div>
                            <div class="ss-preview-actions">
                                <button class="ss-preview-btn"
                                        :style="'background:' + primaryColor + ';color:#fff'">
                                    Generar reporte
                                </button>
                                <span class="ss-preview-badge"
                                      :style="'background:' + accentColor + '22;color:' + accentColor + ';border:1px solid ' + accentColor + '44'">
                                    Activo
                                </span>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:.85rem;padding:.75rem;background:var(--mcm-surface-soft);border:1px solid var(--mcm-border);border-radius:8px;">
                        <p style="color:var(--mcm-muted);font-size:.74rem;line-height:1.5;">
                            Los colores se aplican al guardar. El tema claro/oscuro del panel se controla desde el perfil de usuario.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ─── Sección 3: Configuración operativa ─── --}}
    <div class="ss-card">
        <div class="ss-card-head">
            <span class="ss-card-icon green"><x-heroicon-o-document-chart-bar /></span>
            <div>
                <div class="ss-card-title">Configuración operativa</div>
                <div class="ss-card-sub">Moneda y textos institucionales en reportes exportados.</div>
            </div>
        </div>
        <div class="ss-card-body">
            <div class="ss-field">
                <label class="ss-label">Moneda principal</label>
                <select wire:model="settings.currency" class="ss-input">
                    <option value="COP">COP — Peso colombiano</option>
                    <option value="USD">USD — Dólar estadounidense</option>
                    <option value="EUR">EUR — Euro</option>
                </select>
            </div>
            <div class="ss-field-grid-2">
                <div class="ss-field">
                    <label class="ss-label">Encabezado de reportes</label>
                    <textarea wire:model="settings.header_text" class="ss-input"
                              placeholder="Texto que aparece en la cabecera de reportes exportados…"></textarea>
                </div>
                <div class="ss-field">
                    <label class="ss-label">Pie de página de reportes</label>
                    <textarea wire:model="settings.footer_text" class="ss-input"
                              placeholder="Texto del pie de página en reportes exportados…"></textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Sección 4: Niveles de mora (lectura) ─── --}}
    <div class="ss-card">
        <div class="ss-card-head">
            <span class="ss-card-icon red"><x-heroicon-o-exclamation-triangle /></span>
            <div>
                <div class="ss-card-title">Parametrización de mora activa</div>
                <div class="ss-card-sub">Vista de solo lectura. Edita los umbrales en su módulo dedicado.</div>
            </div>
        </div>
        <div class="ss-card-body">
            @if($sortedRisks->isNotEmpty())
            <div class="ss-risk-bar">
                @foreach($sortedRisks as $risk)
                    @php
                        $col  = $risk->color ?? ($riskColors[$risk->level] ?? '#94a3b8');
                        $maxD = $risk->days_max ?? $maxDays;
                        $flex = max(8, round((($maxD - $risk->days_min) / $maxDays) * 100));
                    @endphp
                    <div class="ss-risk-seg" style="background:{{ $col }};flex:{{ $flex }}"
                         title="{{ $risk->label }}: {{ $risk->days_min }} – {{ $risk->days_max ?? '∞' }} días"></div>
                @endforeach
            </div>
            <div class="ss-risk-legend">
                @foreach($sortedRisks as $risk)
                    @php $col = $risk->color ?? ($riskColors[$risk->level] ?? '#94a3b8'); @endphp
                    <div class="ss-risk-leg">
                        <div class="ss-risk-dot" style="background:{{ $col }}"></div>
                        <span class="ss-risk-leg-txt">
                            <strong>{{ $risk->label }}</strong>
                            {{ $risk->days_min }}{{ $risk->days_max ? '–' . $risk->days_max : '+' }} días
                        </span>
                    </div>
                @endforeach
            </div>
            @else
            <p style="color:var(--mcm-muted);font-size:.83rem;">Sin niveles de mora configurados.</p>
            @endif
        </div>
        <div class="ss-card-foot">
            @php
                $riskUrl = null;
                try { $riskUrl = \App\Filament\Resources\RiskLevelSettingResource::getUrl('index'); } catch (\Throwable) {}
            @endphp
            @if($riskUrl)
            <a href="{{ $riskUrl }}" class="btn-ghost">
                <x-heroicon-o-arrow-top-right-on-square style="width:.9rem;height:.9rem"/>
                Editar umbrales
            </a>
            @endif
        </div>
    </div>

    {{-- ─── Sticky save bar ─── --}}
    <div class="ss-save-sticky">
        <p class="ss-save-hint">
            Los colores y datos de identidad se actualizan en todos los reportes generados a partir de este momento.
        </p>
        <button @click="saveAll()"
                wire:loading.attr="disabled"
                wire:target="saveSettings"
                class="btn-primary">
            <span wire:loading.remove wire:target="saveSettings" class="ss-btn-state">
                <x-heroicon-o-check style="width:.9rem;height:.9rem"/>
                Guardar configuración
            </span>
            <span wire:loading wire:target="saveSettings" class="ss-btn-state">
                <x-heroicon-o-arrow-path style="width:.9rem;height:.9rem;"/>
                Guardando…
            </span>
        </button>
    </div>

</div>
</x-filament-panels::page>
