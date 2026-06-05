@include('filament.operations.partials.load-styles')

@php
    $summary = $page->summaryCards();
    $lastResult = $page->lastResult;
    $resultStatus = $lastResult['status'] ?? null;
    $periodResolution = data_get($lastResult, 'summary.summary.period_resolution');
    $resultBadge = match ($resultStatus) {
        'completed' => 'ops-badge-success',
        'rejected', 'failed' => 'ops-badge-danger',
        'processing' => 'ops-badge-warning',
        default => 'ops-badge-info',
    };
    $uploadFileName = is_object($page->uploadFile) && method_exists($page->uploadFile, 'getClientOriginalName')
        ? $page->uploadFile->getClientOriginalName()
        : null;
@endphp

<div class="ops-shell">
    <section class="ops-hero ops-hero--compact">
        <div>
            <div class="ops-hero-kicker">Operacion de cartera</div>
            <div class="ops-title">Carga de cartera</div>
            <p class="ops-subtitle">
                Sube el corte, valida estructura y activa una nueva version del periodo sin perder trazabilidad.
            </p>
        </div>

        <div class="ops-actions">
            <a class="ops-btn-secondary" href="{{ route('admin.loads.portfolio.template') }}" target="_blank">Plantilla</a>
            @if($page->latestSuccessfulLoadUrl())
                <a class="ops-btn-secondary" href="{{ $page->latestSuccessfulLoadUrl() }}">Ultima carga</a>
            @endif
            <a class="ops-btn-secondary" href="#historial-reciente">Historial</a>
        </div>
    </section>

    <section class="ops-metric-strip" aria-label="Resumen de cargas de cartera">
        <article class="ops-metric">
            <div class="ops-metric-label">Cargas</div>
            <div class="ops-metric-value">{{ number_format($summary['total_loads']) }}</div>
            <div class="ops-metric-sub">Historico operativo</div>
        </article>
        <article class="ops-metric">
            <div class="ops-metric-label">Ultima valida</div>
            <div class="ops-metric-value">{{ $summary['latest_success_label'] }}</div>
            <div class="ops-metric-sub">
                {{ $summary['latest_success_at'] ? $summary['latest_success_at']->format('d/m/Y H:i') : 'Sin version activa' }}
            </div>
        </article>
        <article class="ops-metric">
            <div class="ops-metric-label">Documentos</div>
            <div class="ops-metric-value">{{ number_format($summary['documents_loaded']) }}</div>
            <div class="ops-metric-sub">Procesados correctamente</div>
        </article>
        <article class="ops-metric">
            <div class="ops-metric-label">Saldo cargado</div>
            <div class="ops-metric-value">${{ number_format($summary['historical_amount'], 0, ',', '.') }}</div>
            <div class="ops-metric-sub">Acumulado exitoso</div>
        </article>
    </section>

    <section class="ops-load-layout">
        <article class="ops-upload-card">
            <div class="ops-section-title">Nueva carga</div>
            <p class="ops-section-copy">Adjunta el archivo, define el corte si aplica y deja una nota breve para auditoria.</p>

            <form wire:submit.prevent="submitUpload">
                <div class="ops-upload-grid">
                    <div>
                        <label class="ops-meta-label">Archivo de cartera</label>
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

                    <div class="ops-period-card">
                        <label class="ops-meta-label">Periodo de corte</label>
                        <input class="ops-input" type="month" wire:model="uploadPeriodKey">
                        <div class="ops-period-hint">Opcional si el archivo permite inferirlo.</div>
                        @error('uploadPeriodKey')
                            <div class="ops-error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="ops-field-span">
                        <label class="ops-meta-label">Notas operativas</label>
                        <textarea class="ops-textarea" rows="4" wire:model="uploadNotes" placeholder="Contexto del corte, novedad o comentario para auditoria."></textarea>
                        @error('uploadNotes')
                            <div class="ops-error-text">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="ops-form-footer">
                    <div class="ops-section-copy">Validacion transaccional, versionado por periodo y cierre de documentos ausentes.</div>
                    <button class="ops-btn-primary" type="submit" wire:loading.attr="disabled" wire:target="submitUpload,uploadFile">
                        Validar y procesar
                    </button>
                </div>
            </form>
        </article>

        <aside class="ops-side-stack">
            <div class="ops-panel">
                <div class="ops-section-title">Como preparar el archivo</div>
                <div class="ops-step-list">
                    <div><strong>1. Usa la plantilla.</strong><span>Columnas oficiales: Cuenta, Cliente, NIT, NroDocumento, TipoDocumento, Fechas, ValorDocumento, ImportePendiente, UEN (*_ValorARecaudar) y buckets de mora.</span></div>
                    <div><strong>2. Define el corte.</strong><span>Usa el periodo manual si el archivo mezcla fechas historicas.</span></div>
                    <div><strong>3. Procesa y revisa.</strong><span>Si hay errores, descarga el CSV y corrige solo esas filas.</span></div>
                </div>
            </div>
        </aside>
    </section>

    @if($lastResult)
        <section class="ops-result ops-result--quiet">
            <div class="ops-result-head">
                <div>
                    <div class="ops-section-title">Resultado reciente</div>
                    <p class="ops-section-copy">Resumen del ultimo intento realizado en esta sesion.</p>
                </div>
                <span class="ops-badge {{ $resultBadge }}">
                    {{ match($resultStatus) {
                        'completed' => 'Carga exitosa',
                        'rejected' => 'Carga rechazada',
                        'failed' => 'Carga fallida',
                        default => 'En revision',
                    } }}
                </span>
            </div>

            <div class="ops-meta-grid" style="margin-top: 1rem;">
                <div><div class="ops-meta-label">Referencia</div><div class="ops-meta-value">{{ $lastResult['reference'] }}</div></div>
                <div><div class="ops-meta-label">Periodo</div><div class="ops-meta-value">{{ $lastResult['period_key'] ?? '-' }}</div></div>
                <div><div class="ops-meta-label">Version</div><div class="ops-meta-value">v{{ $lastResult['version'] ?? 0 }}</div></div>
                <div><div class="ops-meta-label">Filas leidas</div><div class="ops-meta-value">{{ number_format($lastResult['total_rows'] ?? 0) }}</div></div>
                <div><div class="ops-meta-label">Filas validas</div><div class="ops-meta-value">{{ number_format($lastResult['valid_rows'] ?? 0) }}</div></div>
                <div><div class="ops-meta-label">Filas con error</div><div class="ops-meta-value">{{ number_format($lastResult['error_rows'] ?? 0) }}</div></div>
            </div>

            @if($periodResolution)
                <div class="ops-preview-list">
                    <div class="ops-preview-item">
                        <strong>Periodo aplicado</strong>
                        <div style="margin-top: 0.35rem;">{{ $periodResolution['label'] ?? 'El sistema aplico una estrategia de resolucion del periodo.' }}</div>
                        @if(!empty($periodResolution['period_key']))
                            <div style="margin-top: 0.35rem;"><strong>{{ $periodResolution['period_key'] }}</strong></div>
                        @endif
                    </div>
                </div>
            @endif

            @if(!empty($lastResult['error_preview']))
                <div class="ops-preview-list">
                    @foreach($lastResult['error_preview'] as $error)
                        <div class="ops-preview-item">
                            <strong>{{ $error['row_number'] ? 'Fila ' . $error['row_number'] : 'Validacion general' }}</strong>
                            <div style="margin-top: 0.35rem;">{{ $error['message'] ?? 'Sin detalle' }}</div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="ops-form-footer">
                <div class="ops-section-copy">Usa el detalle para auditar la version o descargar errores si el archivo fue rechazado.</div>
                <div class="ops-actions">
                    @if(($lastResult['error_rows'] ?? 0) > 0)
                        <a class="ops-btn-secondary" href="{{ route('admin.loads.portfolio.errors', $lastResult['load_id']) }}" target="_blank">Errores CSV</a>
                    @endif
                    @if($page->lastResultUrl())
                        <a class="ops-btn-secondary" href="{{ $page->lastResultUrl() }}">Ver detalle</a>
                    @endif
                </div>
            </div>
        </section>
    @endif

    <div id="historial-reciente" class="ops-history-anchor"></div>
</div>
