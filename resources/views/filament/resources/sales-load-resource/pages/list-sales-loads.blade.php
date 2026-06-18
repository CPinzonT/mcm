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
    $defaultAzureUrl = config('mcm.sales.adls_default_url');
@endphp

<div class="ops-shell">
    <section class="ops-hero ops-hero--compact">
        <div>
            <div class="ops-hero-kicker">Operacion de ventas</div>
            <div class="ops-title">Carga de ventas</div>
            <p class="ops-subtitle">
                Importa ventas desde Azure Data Lake Storage Gen2 o sube un archivo manualmente.
            </p>
        </div>

        <div class="ops-actions">
            <a class="ops-btn-secondary" href="{{ route('admin.loads.sales.template') }}" target="_blank">Plantilla</a>
            @if($page->latestSuccessfulLoadUrl())
                <a class="ops-btn-secondary" href="{{ $page->latestSuccessfulLoadUrl() }}">Ultima carga</a>
            @endif
            <a class="ops-btn-secondary" href="#historial-reciente">Historial</a>
        </div>
    </section>

    <section class="ops-metric-strip" aria-label="Resumen de cargas de ventas">
        <article class="ops-metric">
            <div class="ops-metric-label">Cargas</div>
            <div class="ops-metric-value">{{ number_format($summary['total_loads']) }}</div>
            <div class="ops-metric-sub">Historico operativo</div>
        </article>
        <article class="ops-metric">
            <div class="ops-metric-label">Ultima valida</div>
            <div class="ops-metric-value">{{ $summary['latest_success_label'] }}</div>
            <div class="ops-metric-sub">
                {{ $summary['latest_success_at'] ? $summary['latest_success_at']->format('d/m/Y H:i') : 'Sin carga exitosa' }}
            </div>
        </article>
        <article class="ops-metric">
            <div class="ops-metric-label">Registros</div>
            <div class="ops-metric-value">{{ number_format($summary['rows_loaded']) }}</div>
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
            <div class="ops-section-title">Importar desde Azure</div>
            <p class="ops-section-copy">
                Pega la URL HTTPS con token SAS de Azure Data Lake Storage Gen2. El archivo se descarga, valida y persiste en el sistema.
            </p>

            <form wire:submit.prevent="submitFromAzure">
                <div class="ops-upload-grid ops-upload-grid--simple">
                    <div>
                        <label class="ops-meta-label">URL del archivo en Azure ADLS</label>
                        <input
                            class="ops-textarea"
                            type="url"
                            rows="3"
                            wire:model="azureUrl"
                            placeholder="https://cuenta.dfs.core.windows.net/contenedor/ruta/ventas.xlsx?sv=..."
                            style="min-height: 4.5rem; resize: vertical;"
                        >
                        @if($defaultAzureUrl)
                            <div class="ops-section-copy" style="margin-top: 0.5rem;">
                                URL por defecto cargada desde configuracion. Puedes reemplazarla antes de importar.
                            </div>
                        @endif
                        @error('azureUrl')
                            <div class="ops-error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="ops-meta-label">Notas operativas</label>
                        <textarea class="ops-textarea" rows="4" wire:model="uploadNotes" placeholder="Contexto de la importacion o comentario para auditoria."></textarea>
                        @error('uploadNotes')
                            <div class="ops-error-text">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="ops-form-footer">
                    <div class="ops-section-copy">Solo se aceptan hosts autorizados (.dfs.core.windows.net, .blob.core.windows.net).</div>
                    <button class="ops-btn-primary" type="submit" wire:loading.attr="disabled" wire:target="submitFromAzure">
                        Importar desde Azure
                    </button>
                </div>
            </form>
        </article>

        <aside class="ops-side-stack">
            <div class="ops-panel">
                <div class="ops-section-title">Carga manual (opcional)</div>
                <p class="ops-section-copy">Sube un CSV o Excel si no usas Azure en este momento.</p>

                <form wire:submit.prevent="submitUpload">
                    <label class="ops-meta-label">Archivo de ventas</label>
                    <label class="ops-file-dropzone {{ $uploadFileName ? 'ops-file-dropzone--filled' : '' }}" style="margin-top: 0.5rem;">
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

                    <div class="ops-form-footer" style="margin-top: 1rem;">
                        <button class="ops-btn-secondary" type="submit" wire:loading.attr="disabled" wire:target="submitUpload,uploadFile">
                            Validar y procesar archivo
                        </button>
                    </div>
                </form>
            </div>

            <div class="ops-panel ops-panel-soft">
                <div class="ops-section-title">Columnas esperadas</div>
                <div class="ops-step-list">
                    <div><strong>Cruce con cartera.</strong><span>Cliente, Código de cl, No. Factura y NIT (si aplica).</span></div>
                    <div><strong>Rotación.</strong><span>Indicador: (saldo cartera / ventas 12 meses) × 360.</span></div>
                    <div><strong>Archivo maestro.</strong><span>31 columnas del reporte de ventas (Source.Name … Gestión Com).</span></div>
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
                        'pending' => 'En cola',
                        default => 'En revision',
                    } }}
                </span>
            </div>

            <div class="ops-meta-grid" style="margin-top: 1rem;">
                <div><div class="ops-meta-label">Referencia</div><div class="ops-meta-value">{{ $lastResult['reference'] }}</div></div>
                <div><div class="ops-meta-label">Periodo</div><div class="ops-meta-value">{{ $lastResult['period_key'] ?? '-' }}</div></div>
                <div><div class="ops-meta-label">Filas leidas</div><div class="ops-meta-value">{{ number_format($lastResult['total_rows'] ?? 0) }}</div></div>
                <div><div class="ops-meta-label">Filas procesadas</div><div class="ops-meta-value">{{ number_format($lastResult['processed_rows'] ?? 0) }}</div></div>
                <div><div class="ops-meta-label">Filas con error</div><div class="ops-meta-value">{{ number_format($lastResult['error_rows'] ?? 0) }}</div></div>
                <div><div class="ops-meta-label">Valor</div><div class="ops-meta-value">${{ number_format($lastResult['total_amount'] ?? 0, 0, ',', '.') }}</div></div>
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
                <div class="ops-section-copy">Usa el detalle para auditar la carga o descargar errores si el archivo fue rechazado.</div>
                <div class="ops-actions">
                    @if(($lastResult['error_rows'] ?? 0) > 0)
                        <a class="ops-btn-secondary" href="{{ route('admin.loads.sales.errors', $lastResult['load_id']) }}" target="_blank">Errores CSV</a>
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
