@php
    $traceTypePills = [
        'generacion'  => 'badge-blue',
        'vencimiento' => 'badge-gray',
        'nc'          => 'badge-amber',
        'recaudo'     => 'badge-green',
        'corte'       => 'badge-gray',
    ];
@endphp

@if(empty($timeline))
<div class="vd-empty">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
    </svg>
    Sin eventos de trazabilidad para este documento.
</div>
@else
<div class="vd-trace-timeline">
    @foreach($timeline as $event)
    <div class="vd-trace-item">
        <div class="vd-trace-date">{{ $event['date_label'] }}</div>
        <div class="vd-trace-rail">
            <span class="vd-trace-dot vd-trace-dot--{{ $event['type'] }}"></span>
        </div>
        <div class="vd-trace-body">
            <div class="vd-trace-head">
                <span class="badge-pill {{ $traceTypePills[$event['type']] ?? 'badge-gray' }}" style="font-size:.65rem;">
                    {{ $event['type_label'] }}
                </span>
                <span class="vd-trace-title">{{ $event['title'] }}</span>
                @if(!empty($event['amount_label']))
                <span class="vd-trace-amount {{ ($event['amount_delta'] ?? 0) < 0 ? 'is-out' : 'is-in' }}">
                    {{ $event['amount_label'] }}
                </span>
                @endif
            </div>
            <p class="vd-trace-detail">{{ $event['detail'] }}</p>
            <p class="vd-trace-meta">
                @if(!empty($event['balance_label']))
                    Saldo después: <strong>{{ $event['balance_label'] }}</strong>
                    <span class="sep">·</span>
                @endif
                Fuente: {{ $event['source'] }}
            </p>
        </div>
    </div>
    @endforeach
</div>
@endif
