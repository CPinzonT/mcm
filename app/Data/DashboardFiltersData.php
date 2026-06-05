<?php

namespace App\Data;

/**
 * DTO inmutable de filtros del dashboard estratégico.
 *
 * Supuestos:
 * - Cartera: una sola carga (la activa más reciente por period_date), su corte y solo documentos operativos.
 * - Rango date_from/date_to y comparación por mes → issue_date (fecha de contabilización del Excel).
 * - Sin rango de fechas → toda la cartera del último corte.
 * - Días de mora en KPIs: vencimiento → consultationDate() (Hasta del filtro o hoy).
 * - UEN, regional, channel y advisor_id filtran sobre portfolio_documents vía JOIN con clients/advisors.
 * - client_id filtra directamente sobre portfolio_documents.client_id.
 * - document_types filtra por portfolio_documents.document_type (multi-select).
 */
readonly class DashboardFiltersData
{
    public function __construct(
        public ?string $period    = null,   // YYYY-MM mes de contabilización (comparación A/B)
        public ?string $uen       = null,   // valor único (comparación A/B)
        public ?string $regional  = null,   // valor único (comparación A/B)
        public ?string $channel   = null,   // valor único (comparación A/B)
        public ?int    $advisorId = null,   // único (comparación A/B)
        public ?int    $clientId  = null,
        public ?string $dateFrom  = null,   // YYYY-MM-DD contabilización desde
        public ?string $dateTo    = null,   // YYYY-MM-DD contabilización hasta
        public array   $channels  = [],     // multi-select canal
        public array   $uens      = [],     // multi-select UEN
        public array   $regionals = [],     // multi-select regional
        public array   $advisors  = [],     // multi-select advisor IDs
        /** @var string[] risk_level keys: normal, low, medium, high, critical */
        public array   $riskLevels = [],
        /** @var string[] valores de document_type (FACTURA, NOTA, etc.) */
        public array   $documentTypes = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            period:    $data['period']     ?? null,
            uen:       self::trimNullableString($data['uen'] ?? null),
            regional:  self::trimNullableString($data['regional'] ?? null),
            channel:   self::trimNullableString($data['channel'] ?? null),
            advisorId: isset($data['advisor_id']) && $data['advisor_id'] !== ''
                ? (int) $data['advisor_id'] : null,
            clientId:  isset($data['client_id']) && $data['client_id'] !== ''
                ? (int) $data['client_id'] : null,
            dateFrom:  $data['date_from']  ?? null,
            dateTo:    $data['date_to']    ?? null,
            channels:  self::trimmedStringList($data['channels'] ?? []),
            uens:      self::trimmedStringList($data['uens'] ?? []),
            regionals: self::trimmedStringList($data['regionals'] ?? []),
            advisors:  array_values(array_map('intval', array_filter((array) ($data['advisors'] ?? [])))),
            riskLevels: array_values(array_filter((array) ($data['risk_levels'] ?? []))),
            documentTypes: array_values(array_filter((array) ($data['document_types'] ?? []))),
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
            || !empty($this->advisors)
            || !empty($this->riskLevels)
            || !empty($this->documentTypes);
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

        if ($this->dateTo) {
            $to = \Carbon\Carbon::parse($this->dateTo);

            return $to->copy()->startOfMonth()->format('d/m/Y') . ' – ' . $to->format('d/m/Y');
        }

        if ($this->dateFrom) {
            $from = \Carbon\Carbon::parse($this->dateFrom);

            return $from->format('d/m/Y') . ' – ' . $from->copy()->endOfMonth()->format('d/m/Y');
        }

        return 'Última carga (sin filtro de fechas)';
    }

    /** Fecha de consulta para mora y riesgo (Hasta del filtro contable, o hoy). */
    public function consultationDate(): string
    {
        if ($this->dateTo) {
            return \Carbon\Carbon::parse($this->dateTo)->format('Y-m-d');
        }

        return \Carbon\Carbon::today()->format('Y-m-d');
    }

    /** Primer día del mes del período activo, para cruzar presupuesto. */
    public function budgetPeriodDate(): ?string
    {
        if ($this->period) {
            return \Carbon\Carbon::parse($this->period . '-01')->startOfMonth()->toDateString();
        }
        if ($this->dateFrom) {
            return \Carbon\Carbon::parse($this->dateFrom)->startOfMonth()->toDateString();
        }
        return null;
    }

    /**
     * @param  array<int|string, mixed>  $values
     * @return string[]
     */
    private static function trimmedStringList(array $values): array
    {
        $out = [];
        foreach ($values as $v) {
            if (!is_string($v) && !is_numeric($v)) {
                continue;
            }
            $s = trim((string) $v);
            if ($s !== '') {
                $out[] = $s;
            }
        }

        return array_values(array_unique($out));
    }

    private static function trimNullableString(mixed $v): ?string
    {
        if ($v === null || $v === '') {
            return null;
        }
        $s = trim((string) $v);

        return $s === '' ? null : $s;
    }
}
