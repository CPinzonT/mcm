@include('filament.operations.partials.load-styles')

@php
    $summary = $page->summaryCards();
    $lastResult = $page->lastResult;
    $resultStatus = $lastResult['status'] ?? null;
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
            <div class="ops-hero-kicker">Operacion de recaudos</div>
            <div class="ops-title">Carga de recaudos</div>
            <p class="ops-subtitle">
                Registra recaudos, resuelve el periodo y deja el procesamiento listo para conciliacion.
            </p>
        </div>

        <div class="ops-actions">
            <a class="ops-btn-secondary" href="{{ route('admin.loads.collection.template') }}" target="_blank">Plantilla</a>
            @if($page->latestSuccessfulLoadUrl())
                <a class="ops-btn-secondary" href="{{ $page->latestSuccessfulLoadUrl() }}">Ultima carga</a>
            @endif
            <a class="ops-btn-secondary" href="#historial-reciente">Historial</a>
        </div>
    </section>

    <section class="ops-metric-strip" aria-label="Resumen de cargas de recaudos">
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
            <div class="ops-metric-label">Registros</div>
            <div class="ops-metric-value">{{ number_format($summary['details_loaded']) }}</div>
            <div class="ops-metric-sub">Persistidos correctamente</div>
        </article>
        <article class="ops-metric">
            <div class="ops-metric-label">Valor cargado</div>
            <div class="ops-metric-value">${{ number_format($summary['historical_amount'], 0, ',', '.') }}</div>
            <div class="ops-metric-sub">Acumulado exitoso</div>
        </article>
    </section>

    <section class="ops-load-layout">
        <article class="ops-upload-card">
            <div class="ops-section-title">Nueva carga</div>
            <p class="ops-section-copy">Adjunta el archivo de recaudos y deja una nota corta para seguimiento operativo.</p>

            <form wire:submit.prevent="submitUpload">
                <div class="ops-upload-grid ops-upload-grid--simple">
                    <div>
                        <label class="ops-meta-label">Archivo de recaudos</label>
                        <label class="ops-file-dropzone {{ $uploadFileName ? 'ops-file-dropzone--filled' : '' }}">
                            <input class="ops-file-input" type="file" wire:model="uploadFile" accept=".csv,.xlsx">
                            <span class="ops-file-icon">CSV</span>
                            <span class="ops-file-copy">
                                <span class="ops-file-title">{{ $uploadFileName ?: 'Selecciona o arrastra tu archivo' }}</span>
                                <span class="ops-file-meta">CSV o XLSX / hasta 50 MB</span>
                            </span>
                            <span class="ops-file-action">Buscar archivo</span>
                        </label>
                        <div class="ops-file-loading" wire:loading wire:target="uploadFile">Cargando archivo...</div>
                        @error('uploadFile')
                            <div class="ops-error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="ops-meta-label">Notas operativas</label>
                        <textarea class="ops-textarea" rows="4" wire:model="uploadNotes" placeholder="Contexto del recaudo, novedad o comentario para auditoria."></textarea>
                        @error('uploadNotes')
                            <div class="ops-error-text">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="ops-form-footer">
                    <div class="ops-section-copy">El archivo queda en cola y la tabla se actualiza cuando finalice el procesamiento.</div>
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
                    <div><strong>1. Usa documento y valor.</strong><span>Son los campos minimos para procesar recaudos.</span></div>
                    <div><strong>2. Revisa el periodo.</strong><span>El sistema lo resuelve por mayoria cuando el archivo trae varias fechas.</span></div>
                    <div><strong>3. Consulta el historial.</strong><span>Los estados pendientes y procesados se controlan desde la tabla.</span></div>
                </div>
            </div>

            <details class="ops-guide">
                <summary>Reglas de validacion</summary>
                <div class="ops-guide-body">
                    <div>Columnas minimas: documento y valor_pagado.</div>
                    <div>Aliases soportados: fecha_pago, periodo, nro_recibo, cliente, vendedor y observacion.</div>
                    <div>Si no existe encabezado, se usa el mapeo posicional 1-8 definido para recaudos.</div>
                </div>
            </details>
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
                        'pending' => 'En cola',
                        default => 'En revision',
                    } }}
                </span>
            </div>

            <div class="ops-meta-grid" style="margin-top: 1rem;">
                <div><div class="ops-meta-label">Referencia</div><div class="ops-meta-value">{{ $lastResult['reference'] }}</div></div>
                <div><div class="ops-meta-label">Periodo</div><div class="ops-meta-value">{{ $lastResult['period_key'] ?? '-' }}</div></div>
                <div><div class="ops-meta-label">Version</div><div class="ops-meta-value">v{{ $lastResult['version'] ?? 0 }}</div></div>
                <div><div class="ops-meta-label">Filas leidas</div><div class="ops-meta-value">{{ number_format($lastResult['total_rows'] ?? 0) }}</div></div>
                <div><div class="ops-meta-label">Filas procesadas</div><div class="ops-meta-value">{{ number_format($lastResult['processed_rows'] ?? 0) }}</div></div>
                <div><div class="ops-meta-label">Filas con error</div><div class="ops-meta-value">{{ number_format($lastResult['error_rows'] ?? 0) }}</div></div>
            </div>

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
                        <a class="ops-btn-secondary" href="{{ route('admin.loads.collection.errors', $lastResult['load_id']) }}" target="_blank">Errores CSV</a>
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
