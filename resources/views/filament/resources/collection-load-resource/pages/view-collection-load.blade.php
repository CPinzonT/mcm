@include('filament.operations.partials.load-styles')

@php
    $statusClass = match ($record->status) {
        'completed' => 'ops-badge-success',
        'rejected', 'failed' => 'ops-badge-danger',
        'processing' => 'ops-badge-warning',
        default => 'ops-badge-info',
    };
    $statusLabel = match($record->status) {
        'completed' => 'Completada',
        'rejected' => 'Rechazada',
        'failed' => 'Fallida',
        'cancelled' => 'Anulada',
        default => 'En proceso',
    };
    $previewErrors = $record->errors()->orderBy('row_number')->limit(5)->get();
@endphp

<div class="ops-shell">
    <section class="ops-hero ops-hero--compact">
        <div>
            <div class="ops-hero-kicker">Detalle de recaudos</div>
            <div class="ops-title">{{ $record->reference }}</div>
            <p class="ops-subtitle">
                {{ $record->original_filename ?: basename($record->path) }} · {{ $record->period_key ?: 'Sin periodo' }} · v{{ $record->version }}
            </p>
        </div>

        <div class="ops-actions">
            <span class="ops-badge {{ $statusClass }}">{{ $statusLabel }}</span>
        </div>
    </section>

    <section class="ops-metric-strip" aria-label="Resumen de la carga de recaudos">
        <article class="ops-metric">
            <div class="ops-metric-label">Filas validas</div>
            <div class="ops-metric-value">{{ number_format($record->valid_rows) }}</div>
            <div class="ops-metric-sub">{{ number_format($record->total_rows) }} leidas</div>
        </article>
        <article class="ops-metric">
            <div class="ops-metric-label">Errores</div>
            <div class="ops-metric-value">{{ number_format($record->error_rows) }}</div>
            <div class="ops-metric-sub">Incidencias guardadas</div>
        </article>
        <article class="ops-metric">
            <div class="ops-metric-label">Registros</div>
            <div class="ops-metric-value">{{ number_format($record->detail_count) }}</div>
            <div class="ops-metric-sub">Insertados en recaudos</div>
        </article>
        <article class="ops-metric">
            <div class="ops-metric-label">Valor</div>
            <div class="ops-metric-value">${{ number_format($record->total_collected, 0, ',', '.') }}</div>
            <div class="ops-metric-sub">{{ $record->is_active ? 'Version activa' : 'Version historica' }}</div>
        </article>
    </section>

    <section class="ops-load-layout">
        <article class="ops-panel">
            <div class="ops-section-title">Informacion de la carga</div>
            <p class="ops-section-copy">Datos fuente, versionamiento y auditoria principal.</p>

            <div class="ops-meta-grid" style="margin-top: 1rem;">
                <div><div class="ops-meta-label">Referencia</div><div class="ops-meta-value">{{ $record->reference }}</div></div>
                <div><div class="ops-meta-label">Archivo</div><div class="ops-meta-value">{{ $record->original_filename ?: basename($record->path) }}</div></div>
                <div><div class="ops-meta-label">Periodo</div><div class="ops-meta-value">{{ $record->period_key ?: '-' }}</div></div>
                <div><div class="ops-meta-label">Version</div><div class="ops-meta-value">v{{ $record->version }}</div></div>
                <div><div class="ops-meta-label">Usuario</div><div class="ops-meta-value">{{ $record->uploadedBy?->name ?: '-' }}</div></div>
                <div><div class="ops-meta-label">Procesada</div><div class="ops-meta-value">{{ $record->processed_at?->format('d/m/Y H:i') ?: '-' }}</div></div>
            </div>
        </article>

        <aside class="ops-side-stack">
            <div class="ops-panel ops-panel-soft">
                <div class="ops-section-title">Estado operativo</div>
                <div class="ops-compact-list">
                    <div><strong>{{ $statusLabel }}</strong><span>Estado actual del proceso.</span></div>
                    <div><strong>{{ $record->is_active ? 'Activa' : 'Historica' }}</strong><span>Relacion con el periodo vigente.</span></div>
                </div>
            </div>

            <div class="ops-panel">
                <div class="ops-section-title">Errores y observaciones</div>
                @if($previewErrors->isEmpty())
                    <div class="ops-compact-list">
                        <div>No hay errores guardados para esta carga.</div>
                    </div>
                @else
                    <div class="ops-preview-list">
                        @foreach($previewErrors as $error)
                            <div class="ops-preview-item">
                                <strong>{{ $error->row_number ? 'Fila ' . $error->row_number : 'Validacion general' }}</strong>
                                <div style="margin-top: 0.35rem;">{{ $error->message }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </aside>
    </section>
</div>
