<?php

namespace App\Services;

use App\Models\ClientHistory;
use App\Models\CollectionDetail;
use App\Models\CollectionLoad;
use App\Models\CollectionReconciliation;
use App\Models\PortfolioDocument;
use App\Services\Loads\Support\ImportNormalizer;
use Illuminate\Support\Facades\DB;

class ConciliationService
{
    public const STATUS_MATCHED_FULL    = 'matched_full';
    public const STATUS_MATCHED_PARTIAL = 'matched_partial';
    public const STATUS_OVERPAID        = 'overpaid';
    public const STATUS_NO_INVOICE      = 'no_invoice';
    public const STATUS_NO_PAYMENT      = 'no_payment';
    public const STATUS_TYPE_MISMATCH   = 'type_mismatch';
    /** @deprecated Solo registros historicos; ya no se asigna en conciliacion nueva. */
    public const STATUS_PERIOD_MISMATCH = 'period_mismatch';
    public const STATUS_CLIENT_MISMATCH = 'client_mismatch';
    public const STATUS_SELLER_MISMATCH = 'seller_mismatch';

    public function __construct(
        private readonly ImportNormalizer $importNormalizer,
    ) {}

    public function reconcileLoad(CollectionLoad $load): array
    {
        $stats = [
            self::STATUS_MATCHED_FULL    => 0,
            self::STATUS_MATCHED_PARTIAL => 0,
            self::STATUS_OVERPAID        => 0,
            self::STATUS_NO_INVOICE      => 0,
            self::STATUS_TYPE_MISMATCH   => 0,
            self::STATUS_CLIENT_MISMATCH => 0,
            self::STATUS_SELLER_MISMATCH => 0,
        ];

        CollectionReconciliation::query()
            ->where('collection_load_id', $load->id)
            ->delete();

        $portfolioIndex = $this->buildPortfolioIndex();

        $reconciliations = [];
        $detailUpdates   = [];
        $histories       = [];

        CollectionDetail::query()
            ->where('collection_load_id', $load->id)
            ->select(['id', 'client_id', 'client_name', 'document_number', 'document_type',
                'applied_document_type', 'amount', 'receipt_number', 'payment_date', 'seller_name', 'uen'])
            ->chunkById(500, function ($details) use (
                $load, $portfolioIndex, &$stats, &$reconciliations, &$detailUpdates, &$histories
            ): void {
                foreach ($details as $detail) {
                    [$status, $doc] = $this->matchFromIndex($detail, $portfolioIndex);

                    $applied          = (float) $detail->amount;
                    $portfolioPending = (float) ($doc?->pending_amount ?? 0);
                    $invoiceAmount    = (float) ($doc?->original_amount ?? 0);
                    $difference       = $applied - $portfolioPending;
                    $resulting        = max(0, $portfolioPending - $applied);
                    $bucket           = $this->resolveBucket($doc);

                    $reconciliations[] = [
                        'collection_detail_id'  => $detail->id,
                        'portfolio_document_id' => $doc?->id,
                        'collection_load_id'    => $load->id,
                        'document_number'       => $detail->document_number,
                        'client_portfolio'      => $doc?->client_name_cache ?? null,
                        'client_collection'     => $detail->client_name,
                        'invoice_amount'        => $invoiceAmount,
                        'applied_amount'        => $applied,
                        'portfolio_pending'     => $portfolioPending,
                        'difference'            => $difference,
                        'resulting_balance'     => $resulting,
                        'status'                => $status,
                        'period_portfolio'      => null,
                        'period_collection'     => null,
                        'confidence_level'      => $this->resolveConfidence($detail, $doc),
                        'reconciled_at'         => now()->toDateTimeString(),
                        'created_at'            => now()->toDateTimeString(),
                        'updated_at'            => now()->toDateTimeString(),
                    ];

                    $detailUpdates[$detail->id] = [
                        'reconciliation_status' => $status,
                        'bucket'                => $bucket,
                        'pending_amount_after'  => $doc ? $resulting : null,
                    ];

                    if ($doc && in_array($status, [self::STATUS_MATCHED_FULL, self::STATUS_MATCHED_PARTIAL, self::STATUS_OVERPAID], true)) {
                        $histories[] = [
                            'client_id'             => $detail->client_id,
                            'event_date'            => ($detail->payment_date ?? now())->toDateTimeString(),
                            'event_type'            => 'payment',
                            'amount'                => $applied,
                            'description'           => "Recaudo {$status} — doc {$detail->document_number} — recibo {$detail->receipt_number}",
                            'portfolio_document_id' => $doc->id,
                            'collection_detail_id'  => $detail->id,
                            'created_at'            => now()->toDateTimeString(),
                            'updated_at'            => now()->toDateTimeString(),
                        ];
                    }

                    $stats[$status] = ($stats[$status] ?? 0) + 1;
                }
            });

        foreach (array_chunk($reconciliations, 500) as $chunk) {
            DB::table('collection_reconciliations')->insert($chunk);
        }

        $this->bulkUpdateDetails($detailUpdates);

        foreach (array_chunk($histories, 500) as $chunk) {
            DB::table('client_histories')->insert($chunk);
        }

        return $stats;
    }

    private function buildPortfolioIndex(): array
    {
        $index = [];

        PortfolioDocument::query()
            ->whereHas('portfolioLoad', fn ($q) => $q->where('is_active', true)->where('status', 'completed'))
            ->with(['client:id,name,uen', 'advisor:id,name'])
            ->select(['id', 'client_id', 'advisor_id', 'document_number', 'document_type', 'original_amount',
                'pending_amount', 'days_overdue'])
            ->chunkById(1000, function ($docs) use (&$index): void {
                foreach ($docs as $doc) {
                    $doc->client_name_cache = $doc->client?->name;
                    $doc->seller_name_cache = $doc->advisor?->name;
                    $index[$doc->document_number][] = $doc;
                }
            });

        return $index;
    }

    private function matchFromIndex(object $detail, array $index): array
    {
        $candidates = $index[$detail->document_number] ?? [];

        if (empty($candidates)) {
            return [self::STATUS_NO_INVOICE, null];
        }

        $ranked = $this->rankCandidatesForDetail($candidates, $detail);

        if ($ranked['best'] !== null) {
            return [$this->calcStatus($ranked['best'], $detail), $ranked['best']];
        }

        if ($ranked['client_mismatch'] !== null) {
            return [self::STATUS_CLIENT_MISMATCH, $ranked['client_mismatch']];
        }

        if ($ranked['seller_mismatch'] !== null) {
            return [self::STATUS_SELLER_MISMATCH, $ranked['seller_mismatch']];
        }

        return [self::STATUS_NO_INVOICE, null];
    }

    /**
     * Cruce por numero de documento + cliente + vendedor (+ UEN si viene informada).
     *
     * @param  array<int, object>  $candidates
     * @return array{best: ?object, client_mismatch: ?object, seller_mismatch: ?object}
     */
    private function rankCandidatesForDetail(array $candidates, object $detail): array
    {
        $pool = collect($candidates);

        if (filled($detail->client_name)) {
            $byClient = $pool->filter(
                fn ($doc) => $this->importNormalizer->namesMatch($detail->client_name, $doc->client_name_cache ?? $doc->client?->name)
            );

            if ($byClient->isEmpty()) {
                return ['best' => null, 'client_mismatch' => $pool->first(), 'seller_mismatch' => null];
            }

            $pool = $byClient;
        }

        if (filled($detail->seller_name)) {
            $bySeller = $pool->filter(
                fn ($doc) => $this->importNormalizer->namesMatch($detail->seller_name, $doc->seller_name_cache ?? $doc->advisor?->name)
            );

            if ($bySeller->isEmpty()) {
                return ['best' => null, 'client_mismatch' => null, 'seller_mismatch' => $pool->first()];
            }

            $pool = $bySeller;
        }

        if (filled($detail->uen)) {
            $uen = strtoupper(trim((string) $detail->uen));
            $byUen = $pool->filter(
                fn ($doc) => strtoupper(trim((string) ($doc->client?->uen ?? ''))) === $uen
            );

            if ($byUen->isNotEmpty()) {
                $pool = $byUen;
            }
        }

        $best = $pool->first();

        if ($best !== null) {
            return ['best' => $best, 'client_mismatch' => null, 'seller_mismatch' => null];
        }

        return ['best' => null, 'client_mismatch' => null, 'seller_mismatch' => null];
    }

    private function resolveConfidence(object $detail, ?object $doc): int
    {
        if (! $doc) {
            return 0;
        }

        $score = 40;

        if ($this->importNormalizer->namesMatch($detail->client_name, $doc->client_name_cache ?? $doc->client?->name)) {
            $score += 30;
        }

        if ($this->importNormalizer->namesMatch($detail->seller_name, $doc->seller_name_cache ?? $doc->advisor?->name)) {
            $score += 20;
        }

        if (filled($detail->uen) && strtoupper(trim((string) $detail->uen)) === strtoupper(trim((string) ($doc->client?->uen ?? '')))) {
            $score += 10;
        }

        return min(100, $score);
    }

    private function calcStatus(object $doc, object $detail): string
    {
        $pending = (float) $doc->pending_amount;
        $applied = (float) $detail->amount;

        if ($applied > $pending) {
            return self::STATUS_OVERPAID;
        }

        if (abs($applied - $pending) < 0.01) {
            return self::STATUS_MATCHED_FULL;
        }

        return self::STATUS_MATCHED_PARTIAL;
    }

    private function resolveBucket(?object $doc): ?string
    {
        if (! $doc) {
            return null;
        }

        $days = (int) $doc->days_overdue;

        return match (true) {
            $days <= 0   => 'corriente',
            $days <= 30  => '1-30',
            $days <= 60  => '31-60',
            $days <= 90  => '61-90',
            $days <= 120 => '91-120',
            $days <= 180 => '121-180',
            $days <= 360 => '181-360',
            default      => '+360',
        };
    }

    private function bulkUpdateDetails(array $updates): void
    {
        if (empty($updates)) {
            return;
        }

        foreach (array_chunk($updates, 500, true) as $chunk) {
            $ids = array_keys($chunk);

            $statusCases = '';
            $bucketCases = '';
            $pendingCases = '';

            foreach ($chunk as $id => $data) {
                $status  = addslashes((string) $data['reconciliation_status']);
                $bucket  = $data['bucket'] !== null ? "'" . addslashes((string) $data['bucket']) . "'" : 'NULL';
                $pending = $data['pending_amount_after'] !== null ? (float) $data['pending_amount_after'] : 'NULL';

                $statusCases  .= " WHEN {$id} THEN '{$status}'";
                $bucketCases  .= " WHEN {$id} THEN {$bucket}";
                $pendingCases .= " WHEN {$id} THEN {$pending}";
            }

            $idList = implode(',', $ids);

            DB::statement("
                UPDATE collection_details
                SET
                    reconciliation_status = CASE id {$statusCases} END,
                    bucket = CASE id {$bucketCases} END,
                    pending_amount_after = CASE id {$pendingCases} END
                WHERE id IN ({$idList})
            ");
        }
    }
}
