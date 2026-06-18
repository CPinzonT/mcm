@include('filament.operations.partials.load-styles')

@php
    $summary = $page->summaryCards();
    $recentLoads = $page->recentLoads();
    $lastResult = $page->lastResult;
    $resultStatus = $lastResult['status'] ?? null;
    $resultBadge = match ($resultStatus) {
        'completed' => 'ops-badge-success',
        'rejected' => 'ops-badge-danger',
        default => 'ops-badge-info',
    };
    $uploadFileName = is_object($page->uploadFile) && method_exists($page->uploadFile, 'getClientOriginalName')
        ? $page->uploadFile->getClientOriginalName()
        : null;
@endphp

<div class="ops-shell">
    <section class="ops-hero ops-hero--compact">
        <div>
            <div class="ops-hero-kicker">Maestro de clientes</div>
            <div class="ops-title">Actualización data</div>
            <p class="ops-subtitle">
                Carga el archivo maestro para actualizar teléfonos, correos, direcciones y contactos responsables de clientes existentes.
            </p>
        </div>

        <div class="ops-actions">
            <a class="ops-btn-secondary" href="{{ route('admin.loads.clients.contacts.template') }}" target="_blank">Plantilla</a>
            <a class="ops-btn-secondary" href="{{ \App\Filament\Resources\ClientResource::getUrl('index') }}">Volver a clientes</a>
        </div>
    </section>

    <section class="ops-metric-strip" aria-label="Resumen de actualizaciones de contacto">
        <article class="ops-metric">
            <div class="ops-metric-label">Clientes</div>
            <div class="ops-metric-value">{{ number_format($summary['clients_total']) }}</div>
            <div class="ops-metric-sub">Registrados en el sistema</div>
        </article>
        <article class="ops-metric">
            <div class="ops-metric-label">Cargas</div>
            <div class="ops-metric-value">{{ number_format($summary['total_loads']) }}</div>
            <div class="ops-metric-sub">Actualizaciones realizadas</div>
        </article>
        <article class="ops-metric">
            <div class="ops-metric-label">Ultima valida</div>
            <div class="ops-metric-value">{{ $summary['latest_success_label'] }}</div>
            <div class="ops-metric-sub">
                {{ $summary['latest_success_at'] ? $summary['latest_success_at']->format('d/m/Y H:i') : 'Sin historial' }}
            </div>
        </article>
        <article class="ops-metric">
            <div class="ops-metric-label">Filas actualizadas</div>
            <div class="ops-metric-value">{{ number_format($summary['rows_updated']) }}</div>
            <div class="ops-metric-sub">Acumulado exitoso</div>
        </article>
    </section>

    <section class="ops-load-layout">
        <article class="ops-upload-card">
            <div class="ops-section-title">Archivo maestro</div>
            <p class="ops-section-copy">
                El archivo debe incluir la columna NIT (identificador del cliente) y al menos un dato a actualizar. Solo se modifican clientes ya existentes.
            </p>

            <form wire:submit.prevent="submitUpload">
                <div class="ops-upload-grid ops-upload-grid--simple">
                    <div>
                        <label class="ops-meta-label">Archivo maestro de contactos</label>
                        <label class="ops-file-dropzone {{ $uploadFileName ? 'ops-file-dropzone--filled' : '' }}">
                            <input class="ops-file-input" type="file" wire:model="uploadFile" accept=".csv,.xlsx,.xls">
                            <span class="ops-file-icon">XLS</span>
                            <span class="ops-file-copy">
                                <span class="ops-file-title">{{ $uploadFileName ?: 'Selecciona o arrastra tu archivo' }}</span>
                                <span class="ops-file-meta">CSV, XLSX o XLS / hasta 50 MB</span>
                            </span>
                            <span class="ops-file-action">Buscar archivo</span>
                        </label>
                        <div class="ops-file-loading" wire:loading wire:target="uploadFile">Cargando archivo...</div>
                        @error('uploadFile')
                            <div class="ops-error-text">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="ops-form-footer">
                    <div class="ops-section-copy">Los campos vacíos en el archivo no sobrescriben la información actual del cliente.</div>
                    <button class="ops-btn-primary" type="submit" wire:loading.attr="disabled" wire:target="submitUpload,uploadFile">
                        Procesar actualización
                    </button>
                </div>
            </form>
        </article>

        <aside class="ops-side-stack">
            <div class="ops-panel">
                <div class="ops-section-title">Columnas del maestro</div>
                <div class="ops-step-list">
                    <div><strong>NIT.</strong><span>Identificador obligatorio (con o sin dígito de verificación, ej. 901276722-2).</span></div>
                    <div><strong>Datos del maestro.</strong><span>Nombre SN, fecha de creación, canal, territorio, límite de crédito, teléfono móvil, correo, ciudad y dirección.</span></div>
                    <div><strong>Coincidencia.</strong><span>Primero por NIT; si no existe, por nombre (razón social). Si el cliente tiene NIT temporal REC-/IMP-, se reemplaza por el NIT del maestro.</span></div>
                </div>
            </div>

            <div class="ops-panel ops-panel-soft">
                <div class="ops-section-title">Historial reciente</div>
                @if($recentLoads->isEmpty())
                    <div class="ops-section-copy">Aún no hay actualizaciones registradas.</div>
                @else
                    <div class="ops-compact-list">
                        @foreach($recentLoads as $load)
                            <div>
                                <strong>{{ $load->reference }}</strong>
                                <span>
                                    {{ $load->updated_rows }} actualizados
                                    @if($load->not_found_rows > 0)
                                        · {{ $load->not_found_rows }} no encontrados
                                    @endif
                                    · {{ $load->processed_at?->format('d/m/Y H:i') ?: $load->created_at->format('d/m/Y H:i') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </aside>
    </section>

    @if($lastResult)
        <section class="ops-result ops-result--quiet">
            <div class="ops-result-head">
                <div>
                    <div class="ops-section-title">Resultado reciente</div>
                    <p class="ops-section-copy">Resumen del último procesamiento en esta sesión.</p>
                </div>
                <span class="ops-badge {{ $resultBadge }}">
                    {{ match($resultStatus) {
                        'completed' => 'Actualización exitosa',
                        'rejected' => 'Actualización rechazada',
                        default => 'En revisión',
                    } }}
                </span>
            </div>

            <div class="ops-meta-grid" style="margin-top: 1rem;">
                <div><div class="ops-meta-label">Referencia</div><div class="ops-meta-value">{{ $lastResult['reference'] }}</div></div>
                <div><div class="ops-meta-label">Actualizados</div><div class="ops-meta-value">{{ number_format($lastResult['updated_rows'] ?? 0) }}</div></div>
                <div><div class="ops-meta-label">No encontrados</div><div class="ops-meta-value">{{ number_format($lastResult['not_found_rows'] ?? 0) }}</div></div>
                <div><div class="ops-meta-label">Errores</div><div class="ops-meta-value">{{ number_format($lastResult['error_rows'] ?? 0) }}</div></div>
                <div><div class="ops-meta-label">Omitidos</div><div class="ops-meta-value">{{ number_format($lastResult['skipped_rows'] ?? 0) }}</div></div>
            </div>

            @if(!empty($lastResult['error_preview']))
                <div class="ops-preview-list">
                    @foreach($lastResult['error_preview'] as $error)
                        <div class="ops-preview-item">
                            <strong>{{ ($error['row_number'] ?? null) ? 'Fila ' . $error['row_number'] : 'Validación general' }}</strong>
                            <div style="margin-top: 0.35rem;">{{ $error['message'] ?? 'Sin detalle' }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    @endif
</div>
