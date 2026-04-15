<?php

namespace App\Data;

/**
 * DTO inmutable de filtros del dashboard estratégico.
 *
 * Supuestos:
 * - Si period es null y date_from/date_to también son null, se usa el período más reciente.
 * - period y date_from/date_to son mutuamente excluyentes: period tiene prioridad.
 * - UEN, regional, channel y advisor_id filtran sobre portfolio_documents vía JOIN con clients/advisors.
 * - client_id filtra directamente sobre portfolio_documents.client_id.
 */
readonly class DashboardFiltersData
{
    public function __construct(
        public ?string $period    = null,   // YYYY-MM único (comparación A/B)
        public ?string $uen       = null,   // valor único (comparación A/B)
        public ?string $regional  = null,   // valor único (comparación A/B)
        public ?string $channel   = null,   // valor único (comparación A/B)
        public ?int    $advisorId = null,   // único (comparación A/B)
        public ?int    $clientId  = null,
        public ?string $dateFrom  = null,   // YYYY-MM-DD
        public ?string $dateTo    = null,   // YYYY-MM-DD
        public array   $channels  = [],     // multi-select canal
        public array   $uens      = [],     // multi-select UEN
        public array   $regionals = [],     // multi-select regional
        public array   $periods   = [],     // multi-select períodos YYYY-MM (filtra por issue_date)
        public array   $advisors  = [],     // multi-select advisor IDs
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            period:    $data['period']     ?? null,
            uen:       $data['uen']        ?? null,
            regional:  $data['regional']   ?? null,
            channel:   $data['channel']    ?? null,
            advisorId: isset($data['advisor_id']) && $data['advisor_id'] !== ''
                ? (int) $data['advisor_id'] : null,
            clientId:  isset($data['client_id']) && $data['client_id'] !== ''
                ? (int) $data['client_id'] : null,
            dateFrom:  $data['date_from']  ?? null,
            dateTo:    $data['date_to']    ?? null,
            channels:  array_values(array_filter((array) ($data['channels']  ?? []))),
            uens:      array_values(array_filter((array) ($data['uens']      ?? []))),
            regionals: array_values(array_filter((array) ($data['regionals'] ?? []))),
            periods:   array_values(array_filter((array) ($data['periods']   ?? []))),
            advisors:  array_values(array_map('intval', array_filter((array) ($data['advisors'] ?? [])))),
        );
    }

    /** Retorna true si al menos un filtro de dimensión está activo. */
    public function hasFilters(): bool
    {
        return $this->period !== null
            || $this->uen !== null
            || $this->regional !== null
            || $this->channel !== null
            || $this->advisorId !== null
            || $this->clientId !== null
            || $this->dateFrom !== null
            || $this->dateTo !== null
            || !empty($this->periods)
            || !empty($this->advisors);
    }

    /** Etiqueta legible del período activo. */
    public function periodLabel(): string
    {
        if ($this->period) {
            return \Carbon\Carbon::parse($this->period)->translatedFormat('M Y');
        }
        if ($this->dateFrom && $this->dateTo) {
            $from = \Carbon\Carbon::parse($this->dateFrom)->format('d/m/Y');
            $to   = \Carbon\Carbon::parse($this->dateTo)->format('d/m/Y');
            return "{$from} – {$to}";
        }
        return 'Período más reciente';
    }

    /** Primer día del mes del período activo, para cruzar presupuesto. */
    public function budgetPeriodDate(): ?string
    {
        // Multi-select: usa el período más reciente seleccionado
        if (!empty($this->periods)) {
            $sorted = $this->periods;
            rsort($sorted);
            return \Carbon\Carbon::parse($sorted[0] . '-01')->startOfMonth()->toDateString();
        }
        if ($this->period) {
            return \Carbon\Carbon::parse($this->period . '-01')->startOfMonth()->toDateString();
        }
        if ($this->dateFrom) {
            return \Carbon\Carbon::parse($this->dateFrom)->startOfMonth()->toDateString();
        }
        return null;
    }
}
