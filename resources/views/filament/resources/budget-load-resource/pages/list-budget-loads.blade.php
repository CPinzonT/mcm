@include('filament.pages.partials.modern-dashboard-styles')
@include('filament.operations.partials.load-styles')

@php
    $s = $page->summary;
    $uploadFileName = is_object($page->uploadFile) && method_exists($page->uploadFile, 'getClientOriginalName')
        ? $page->uploadFile->getClientOriginalName()
        : null;
@endphp

<div class="ops-shell mcm-modern-page">
    <section class="ops-hero ops-hero--compact">
        <div>
            <div class="ops-hero-kicker">Operación de presupuesto</div>
            <div class="ops-title">Presupuesto de recaudo</div>
            <p class="ops-subtitle">
                Filtra los datos cargados, importa archivos con PPTO y RECAUDO, y administra el historial de cargas.
            </p>
        </div>
        <div class="ops-actions">
            <button type="button" wire:click="resetFilters" class="ops-btn-secondary">Limpiar filtros</button>
            <a class="ops-btn-secondary" href="#historial-reciente">Historial</a>
        </div>
    </section>

    <section class="ops-metric-strip" aria-label="KPIs de presupuesto">
        <article class="ops-metric">
            <div class="ops-metric-label">PPTO</div>
            <div class="ops-metric-value">
                @if($s['budget_total'] !== null)
                    ${{ number_format($s['budget_total'], 0, ',', '.') }}
                @else
                    —
                @endif
            </div>
            <div class="ops-metric-sub">{{ $s['budget_rows'] }} filas</div>
        </article>
        <article class="ops-metric">
            <div class="ops-metric-label">Recaudo</div>
            <div class="ops-metric-value">
                @if($s['recaudo_total'] !== null)
                    ${{ number_format($s['recaudo_total'], 0, ',', '.') }}
                @else
                    —
                @endif
            </div>
            <div class="ops-metric-sub">Columna RECAUDO del archivo</div>
        </article>
        <article class="ops-metric">
            <div class="ops-metric-label">% Cumplimiento</div>
            <div class="ops-metric-value" style="color:{{ ($s['cumplimiento_pct'] ?? 0) >= 100 ? 'var(--mcm-green)' : (($s['cumplimiento_pct'] ?? 0) >= 80 ? 'var(--mcm-amber)' : 'var(--mcm-red)') }}">
                @if($s['cumplimiento_pct'] !== null)
                    {{ number_format($s['cumplimiento_pct'], 1, ',', '.') }}%
                @else
                    —
                @endif
            </div>
            <div class="ops-metric-sub">Recaudo / PPTO</div>
        </article>
        <article class="ops-metric">
            <div class="ops-metric-label">Brecha</div>
            <div class="ops-metric-value">
                @if($s['brecha'] !== null)
                    ${{ number_format($s['brecha'], 0, ',', '.') }}
                @else
                    —
                @endif
            </div>
            <div class="ops-metric-sub">Recaudo menos meta</div>
        </article>
        <article class="ops-metric">
            <div class="ops-metric-label">Saldo debido</div>
            <div class="ops-metric-value">
                @if($s['balance_due_total'] !== null)
                    ${{ number_format($s['balance_due_total'], 0, ',', '.') }}
                @else
                    —
                @endif
            </div>
            <div class="ops-metric-sub">Suma SALDO DEBIDO</div>
        </article>
        <article class="ops-metric">
            <div class="ops-metric-label">Última carga</div>
            <div class="ops-metric-value" style="font-size:1rem;">
                {{ $s['latest_load']['at'] ?? '—' }}
            </div>
            <div class="ops-metric-sub">{{ $s['latest_load']['filename'] ?? 'Sin cargas' }}</div>
        </article>
    </section>

    <section class="ops-panel" style="padding:1rem;">
        <div class="ops-section-title" style="margin-bottom:.65rem;">Filtros de análisis</div>
        <div class="sd-filters-grid sd-filters-grid--budget">
            @include('filament.pages.partials.budget-filters-dimensions')
            <div class="sd-filter-card sd-checklist-section">
                <div class="sd-checklist-title">
                    Período
                    @if(count($page->selectedPeriods) > 0)
                        <span style="font-size:.6rem;color:var(--mcm-accent);font-weight:700;margin-left:.3rem;">({{ count($page->selectedPeriods) }})</span>
                    @endif
                </div>
                <div class="sd-checklist-items">
                    @foreach($page->periodOptionsShort as $ym => $lbl)
                    <label class="sd-check-item" wire:key="budget-period-{{ $ym }}">
                        <input type="checkbox" {{ in_array($ym, $page->selectedPeriods, true) ? 'checked' : '' }} wire:click='togglePeriod(@json($ym))'>
                        {{ $lbl }}
                    </label>
                    @endforeach
                </div>
            </div>
            <div class="sd-filter-card sd-checklist-section">
                <div class="sd-checklist-title">
                    Tipo transacción
                    @if(count($page->selectedTransactionTypes) > 0)
                        <span style="font-size:.6rem;color:var(--mcm-accent);font-weight:700;margin-left:.3rem;">({{ count($page->selectedTransactionTypes) }})</span>
                    @endif
                </div>
                <div class="sd-checklist-items">
                    @foreach($page->transactionTypeOptions as $val => $lbl)
                    <label class="sd-check-item" wire:key="budget-tx-{{ md5((string) $val) }}">
                        <input type="checkbox" {{ in_array(trim((string) $val), array_map('trim', $page->selectedTransactionTypes), true) ? 'checked' : '' }} wire:click='toggleTransactionType(@json($val))'>
                        {{ $lbl }}
                    </label>
                    @endforeach
                </div>
            </div>
            <div class="sd-filter-card sd-checklist-section">
                <div class="sd-checklist-title">
                    Categoría
                    @if(count($page->selectedCategories) > 0)
                        <span style="font-size:.6rem;color:var(--mcm-accent);font-weight:700;margin-left:.3rem;">({{ count($page->selectedCategories) }})</span>
                    @endif
                </div>
                <div class="sd-checklist-items">
                    @foreach($page->categoryOptions as $val => $lbl)
                    <label class="sd-check-item" wire:key="budget-cat-{{ md5((string) $val) }}">
                        <input type="checkbox" {{ in_array(trim((string) $val), array_map('trim', $page->selectedCategories), true) ? 'checked' : '' }} wire:click='toggleCategory(@json($val))'>
                        {{ $lbl }}
                    </label>
                    @endforeach
                </div>
            </div>
            <div class="sd-filter-card sd-checklist-section sd-daterange-block">
                <label class="sd-daterange-title">Rango de fechas</label>
                <div style="margin-bottom:.4rem;">
                    <label style="font-size:.62rem;color:var(--mcm-muted);font-weight:600;display:block;margin-bottom:.2rem;">Campo</label>
                    <select wire:model.live="dateField" class="sd-filter-input" style="width:100%;">
                        <option value="application_date">Fecha de aplicación</option>
                        <option value="invoice_date">Fecha factura</option>
                        <option value="due_date">Fecha vencimiento</option>
                    </select>
                </div>
                <div class="sd-daterange-row">
                    <div class="sd-filter-col">
                        <label style="font-size:.62rem;color:var(--mcm-muted);font-weight:600;">Desde</label>
                        <input type="date" wire:model.live="dateFrom" class="sd-filter-input" style="width:100%;">
                    </div>
                    <div class="sd-filter-col">
                        <label style="font-size:.62rem;color:var(--mcm-muted);font-weight:600;">Hasta</label>
                        <input type="date" wire:model.live="dateTo" class="sd-filter-input" style="width:100%;">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <article class="ops-upload-card">
        <div class="ops-section-title">Nueva carga</div>
        <p class="ops-section-copy">
            Excel o CSV con NOMBRE CLIENTE, REGIONAL, DESC CANAL, VENDEDOR, TIPO TRANSACCION, PPTO, RECAUDO, CATEGORIAS y fechas.
        </p>

        <form wire:submit.prevent="submitUpload">
            <div class="ops-upload-grid ops-upload-grid--simple">
                <div>
                    <label class="ops-meta-label">Archivo</label>
                    <label class="ops-file-dropzone {{ $uploadFileName ? 'ops-file-dropzone--filled' : '' }}">
                        <input class="ops-file-input" type="file" wire:model="uploadFile" accept=".csv,.xlsx,.xls">
                        <span class="ops-file-icon">XLS</span>
                        <span class="ops-file-copy">
                            <span class="ops-file-title">{{ $uploadFileName ?: 'Selecciona o arrastra tu archivo' }}</span>
                            <span class="ops-file-meta">CSV o Excel · hasta 50 MB</span>
                        </span>
                        <span class="ops-file-action">Buscar</span>
                    </label>
                    @error('uploadFile')<div class="ops-error-text">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="ops-meta-label">Período forzado (opcional)</label>
                    <input type="text" class="ops-textarea" style="min-height:2.35rem;padding:.45rem .65rem;" wire:model="uploadPeriodKey" placeholder="YYYY-MM">
                    @error('uploadPeriodKey')<div class="ops-error-text">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="ops-meta-label">Notas</label>
                    <textarea class="ops-textarea" rows="3" wire:model="uploadNotes" placeholder="Comentario de la carga."></textarea>
                </div>
            </div>
            <div class="ops-form-footer">
                <div class="ops-section-copy">Al recargar un período, se reemplazan las filas de ese mes.</div>
                <button class="ops-btn-primary" type="submit" wire:loading.attr="disabled">Importar presupuesto</button>
            </div>
        </form>

        @if($page->lastUpload)
        <div class="ops-panel" style="margin-top:1rem;">
            <div class="ops-section-title">Resultado reciente</div>
            <div class="ops-section-copy">
                {{ $page->lastUpload['reference'] }} · {{ $page->lastUpload['valid_rows'] }} filas
                @if(isset($page->lastUpload['total_ppto']))
                    · PPTO ${{ number_format($page->lastUpload['total_ppto'], 0, ',', '.') }}
                @endif
                @if(($page->lastUpload['total_recaudo'] ?? 0) > 0)
                    · Recaudo ${{ number_format($page->lastUpload['total_recaudo'], 0, ',', '.') }}
                @endif
                @if($page->lastUpload['period_key'] ?? null) · {{ $page->lastUpload['period_key'] }} @endif
            </div>
        </div>
        @endif
    </article>

    <div id="historial-reciente" class="ops-history-anchor"></div>
</div>
