        <div class="sd-filter-card sd-filter-card--wide sd-checklist-section">
            <div class="sd-checklist-title">
                Cliente
                @if(count($this->selectedClients) > 0)
                    <span style="font-size:.6rem;color:var(--mcm-accent);font-weight:700;margin-left:.3rem;">({{ count($this->selectedClients) }})</span>
                @endif
            </div>
            <input type="text" wire:model.live.debounce.300ms="clientSearch" class="sd-filter-search" placeholder="Buscar cliente...">
            <div class="sd-checklist-items">
                @foreach($this->clientOptions as $val => $lbl)
                <label class="sd-check-item" wire:key="budget-client-{{ md5((string) $val) }}">
                    <input type="checkbox" {{ in_array(trim((string) $val), array_map('trim', $this->selectedClients), true) ? 'checked' : '' }} wire:click='toggleClient(@json($val))'>
                    {{ $lbl }}
                </label>
                @endforeach
            </div>
        </div>

        <div class="sd-filter-card sd-checklist-section">
            <div class="sd-checklist-title">
                Regional
                @if(count($this->selectedRegionals) > 0)
                    <span style="font-size:.6rem;color:var(--mcm-accent);font-weight:700;margin-left:.3rem;">({{ count($this->selectedRegionals) }})</span>
                @endif
            </div>
            <div class="sd-checklist-items">
                @foreach($this->regionalOptions as $val => $lbl)
                <label class="sd-check-item" wire:key="budget-reg-{{ md5((string) $val) }}">
                    <input type="checkbox" {{ in_array(trim((string) $val), array_map('trim', $this->selectedRegionals), true) ? 'checked' : '' }} wire:click='toggleRegional(@json($val))'>
                    {{ $lbl }}
                </label>
                @endforeach
            </div>
        </div>

        <div class="sd-filter-card sd-checklist-section">
            <div class="sd-checklist-title">
                Canal
                @if(count($this->selectedChannels) > 0)
                    <span style="font-size:.6rem;color:var(--mcm-accent);font-weight:700;margin-left:.3rem;">({{ count($this->selectedChannels) }})</span>
                @endif
            </div>
            <div class="sd-checklist-items">
                @foreach($this->channelOptions as $val => $lbl)
                <label class="sd-check-item" wire:key="budget-ch-{{ md5((string) $val) }}">
                    <input type="checkbox" {{ in_array(trim((string) $val), array_map('trim', $this->selectedChannels), true) ? 'checked' : '' }} wire:click='toggleChannel(@json($val))'>
                    {{ $lbl }}
                </label>
                @endforeach
            </div>
        </div>

        <div class="sd-filter-card sd-checklist-section">
            <div class="sd-checklist-title">
                Vendedor
                @if(count($this->selectedSellers) > 0)
                    <span style="font-size:.6rem;color:var(--mcm-accent);font-weight:700;margin-left:.3rem;">({{ count($this->selectedSellers) }})</span>
                @endif
            </div>
            <input type="text" wire:model.live.debounce.300ms="sellerSearch" class="sd-filter-search" placeholder="Buscar vendedor...">
            <div class="sd-checklist-items">
                @foreach($this->sellerOptions as $val => $lbl)
                <label class="sd-check-item" wire:key="budget-seller-{{ md5((string) $val) }}">
                    <input type="checkbox" {{ in_array(trim((string) $val), array_map('trim', $this->selectedSellers), true) ? 'checked' : '' }} wire:click='toggleSeller(@json($val))'>
                    {{ $lbl }}
                </label>
                @endforeach
            </div>
        </div>
