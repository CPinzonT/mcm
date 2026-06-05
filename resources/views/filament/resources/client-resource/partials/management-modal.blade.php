@php
    $c = $this->record;
@endphp
<div class="cv-mgmt-backdrop" wire:click.self="closeManagementModal">
    <div class="cv-mgmt-modal" role="dialog" aria-modal="true" aria-labelledby="cv-mgmt-title">
        <div class="cv-mgmt-head">
            <div>
                <p class="cv-mgmt-kicker">Registrar gestión / compromiso</p>
                <h2 id="cv-mgmt-title" class="cv-mgmt-title">{{ $mgmtDocLabel }}</h2>
                <p class="cv-mgmt-sub">{{ $c->name }} · UEN {{ $c->uen ?? '—' }} · {{ $c->channel ?? '—' }}</p>
            </div>
            <button type="button" class="btn-ghost" wire:click="closeManagementModal" aria-label="Cerrar">✕</button>
        </div>

        <div class="cv-mgmt-body">
            <div class="cv-mgmt-types">
                @foreach(['call'=>'Llamada','email'=>'Correo','visit'=>'Visita','agreement'=>'Acuerdo','legal'=>'Jurídico','other'=>'Otro'] as $tk=>$tl)
                <button type="button"
                        class="cv-mgmt-type {{ $mgType === $tk ? 'active' : '' }}"
                        wire:click="$set('mgType', '{{ $tk }}')">{{ $tl }}</button>
                @endforeach
            </div>

            <div class="cv-mgmt-grid">
                <div class="cv-mgmt-full">
                    <label class="filter-label">Tipo de acuerdo / asunto *</label>
                    <input type="text" wire:model="mgSubject" class="filter-input" placeholder="Ej. Pendiente cruce, envío correo a logística">
                    @error('mgSubject') <p class="cv-mgmt-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="filter-label">Fecha *</label>
                    <input type="date" wire:model="mgContactDate" class="filter-input">
                    @error('mgContactDate') <p class="cv-mgmt-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="filter-label">Hora *</label>
                    <input type="time" wire:model="mgContactTime" class="filter-input">
                    @error('mgContactTime') <p class="cv-mgmt-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="filter-label">Resultado</label>
                    <select wire:model="mgResult" class="filter-input">
                        <option value="">Sin resultado</option>
                        <option value="arrangement">Acuerdo</option>
                        <option value="promise_to_pay">Promesa de pago</option>
                        <option value="partial_payment">Pago parcial</option>
                        <option value="no_contact">Sin contacto</option>
                        <option value="refused">Rechazó</option>
                        <option value="other">Otro</option>
                    </select>
                </div>
                <div>
                    <label class="filter-label">Fecha compromiso</label>
                    <input type="date" wire:model="mgPromisedDate" class="filter-input">
                </div>
                <div class="cv-mgmt-full">
                    <label class="filter-label">Observación *</label>
                    <textarea wire:model="mgDescription" class="filter-input" rows="4" placeholder="Detalle de la gestión o compromiso…"></textarea>
                    @error('mgDescription') <p class="cv-mgmt-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="cv-mgmt-foot">
            <button type="button" class="btn-ghost" wire:click="closeManagementModal">Cancelar</button>
            <button type="button" class="btn-primary" wire:click="saveManagement" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="saveManagement">Guardar gestión</span>
                <span wire:loading wire:target="saveManagement">Guardando…</span>
            </button>
        </div>
    </div>
</div>
