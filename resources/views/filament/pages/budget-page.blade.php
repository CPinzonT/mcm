<x-filament-panels::page>

@include('filament.pages.partials.modern-dashboard-styles')
@include('filament.operations.partials.load-styles')

@php
    $s = $this->summary;
    $uploadFileName = is_object($this->uploadFile) && method_exists($this->uploadFile, 'getClientOriginalName')
        ? $this->uploadFile->getClientOriginalName()
        : null;
@endphp

<div class="mcm-modern-page space-y-4">
    <section class="pbi-header" style="margin-bottom:0;">
        <div class="pbi-header-logo" style="font-size:1rem;font-weight:900;">
            Presupuesto
            <small>Archivo detallado · PPTO y recaudo</small>
        </div>
        <div class="pbi-header-divider"></div>
        <div class="pbi-header-kpi">
            <span class="pbi-header-kpi-value">{{ $s['period_label'] }}</span>
            <span class="pbi-header-kpi-label">Período analizado</span>
        </div>
        <div class="pbi-header-actions">
            <button type="button" wire:click="resetFilters" class="pbi-header-btn">Limpiar filtros</button>
            <a href="{{ route('admin.loads.budget.template') }}" target="_blank" class="pbi-header-btn">Plantilla Excel</a>
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

    <div class="sd-filters-grid sd-filters-grid--budget">
        @include('filament.pages.partials.budget-filters-dimensions')
        <div class="sd-filter-card sd-checklist-section">
            <div class="sd-checklist-title">
                Período
                @if(count($this->selectedPeriods) > 0)
                    <span style="font-size:.6rem;color:var(--mcm-accent);font-weight:700;margin-left:.3rem;">({{ count($this->selectedPeriods) }})</span>
                @endif
            </div>
            <div class="sd-checklist-items">
                @foreach($this->periodOptionsShort as $ym => $lbl)
                <label class="sd-check-item" wire:key="budget-period-{{ $ym }}">
                    <input type="checkbox" {{ in_array($ym, $this->selectedPeriods, true) ? 'checked' : '' }} wire:click='togglePeriod(@json($ym))'>
                    {{ $lbl }}
                </label>
                @endforeach
            </div>
        </div>

        <div class="sd-filter-card sd-checklist-section">
            <div class="sd-checklist-title">
                Tipo transacción
                @if(count($this->selectedTransactionTypes) > 0)
                    <span style="font-size:.6rem;color:var(--mcm-accent);font-weight:700;margin-left:.3rem;">({{ count($this->selectedTransactionTypes) }})</span>
                @endif
            </div>
            <div class="sd-checklist-items">
                @foreach($this->transactionTypeOptions as $val => $lbl)
                <label class="sd-check-item" wire:key="budget-tx-{{ md5((string) $val) }}">
                    <input type="checkbox" {{ in_array(trim((string) $val), array_map('trim', $this->selectedTransactionTypes), true) ? 'checked' : '' }} wire:click='toggleTransactionType(@json($val))'>
                    {{ $lbl }}
                </label>
                @endforeach
            </div>
        </div>

        <div class="sd-filter-card sd-checklist-section">
            <div class="sd-checklist-title">
                Categoría
                @if(count($this->selectedCategories) > 0)
                    <span style="font-size:.6rem;color:var(--mcm-accent);font-weight:700;margin-left:.3rem;">({{ count($this->selectedCategories) }})</span>
                @endif
            </div>
            <div class="sd-checklist-items">
                @foreach($this->categoryOptions as $val => $lbl)
                <label class="sd-check-item" wire:key="budget-cat-{{ md5((string) $val) }}">
                    <input type="checkbox" {{ in_array(trim((string) $val), array_map('trim', $this->selectedCategories), true) ? 'checked' : '' }} wire:click='toggleCategory(@json($val))'>
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

    <section class="ops-load-layout">
        <article class="ops-upload-card">
            <div class="ops-section-title">Cargar archivo de presupuesto</div>
            <p class="ops-section-copy">
                Excel o CSV con columnas del reporte: NOMBRE CLIENTE, REGIONAL, DESC CANAL, VENDEDOR,
                TIPO TRANSAC, NO FACTURA, FECHAS, montos de aging,
                <strong>PPTO</strong>, <strong>RECAUDO</strong>, CATEGORÍAS y fecha de aplicación.
                Descarga la plantilla para ver el formato esperado.
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
                        <p class="ops-section-copy" style="margin-top:.35rem;">Si no se indica, se deduce de la fecha de aplicación o factura.</p>
                    </div>
                    <div>
                        <label class="ops-meta-label">Notas</label>
                        <textarea class="ops-textarea" rows="3" wire:model="uploadNotes" placeholder="Comentario de la carga."></textarea>
                    </div>
                </div>
                <div class="ops-form-footer">
                    <div class="ops-section-copy">Al recargar un período, se reemplazan las filas de ese mes en el sistema.</div>
                    <button class="ops-btn-primary" type="submit" wire:loading.attr="disabled">Importar presupuesto</button>
                </div>
            </form>

            @if($lastUpload)
            <div class="ops-panel" style="margin-top:1rem;">
                <div class="ops-section-title">Resultado de la carga</div>
                <div class="ops-section-copy">
                    {{ $lastUpload['reference'] }} · {{ $lastUpload['valid_rows'] }} filas
                    @if(isset($lastUpload['total_ppto']))
                        · PPTO ${{ number_format($lastUpload['total_ppto'], 0, ',', '.') }}
                    @endif
                    @if(($lastUpload['total_recaudo'] ?? 0) > 0)
                        · Recaudo ${{ number_format($lastUpload['total_recaudo'], 0, ',', '.') }}
                    @endif
                    @if($lastUpload['period_key'] ?? null) · {{ $lastUpload['period_key'] }} @endif
                </div>
            </div>
            @endif
        </article>

        <aside class="ops-side-stack">
            <div class="ops-panel">
                <div class="ops-section-title">Por categoría</div>
                @forelse($s['by_category'] as $row)
                <div style="display:flex;justify-content:space-between;gap:.5rem;font-size:.78rem;padding:.3rem 0;border-bottom:1px solid var(--mcm-border);">
                    <span style="min-width:0;">{{ $row['label'] }}</span>
                    <span class="money-value" style="white-space:nowrap;">
                        PPTO ${{ number_format($row['ppto'], 0, ',', '.') }}
                        · R ${{ number_format($row['recaudo'], 0, ',', '.') }}
                    </span>
                </div>
                @empty
                <p class="ops-section-copy">Sin datos para los filtros actuales.</p>
                @endforelse
            </div>
            <div class="ops-panel">
                <div class="ops-section-title">Por tipo transacción</div>
                @forelse($s['by_transaction_type'] as $row)
                <div style="display:flex;justify-content:space-between;gap:.5rem;font-size:.78rem;padding:.3rem 0;border-bottom:1px solid var(--mcm-border);">
                    <span style="min-width:0;">{{ $row['label'] }}</span>
                    <span class="money-value" style="white-space:nowrap;">
                        PPTO ${{ number_format($row['ppto'], 0, ',', '.') }}
                        · R ${{ number_format($row['recaudo'], 0, ',', '.') }}
                    </span>
                </div>
                @empty
                <p class="ops-section-copy">Sin datos para los filtros actuales.</p>
                @endforelse
            </div>
        </aside>
    </section>
</div>

</x-filament-panels::page>
