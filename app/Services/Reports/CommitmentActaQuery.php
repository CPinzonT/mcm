<?php

namespace App\Services\Reports;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class CommitmentActaQuery
{
    /**
     * @return array{0: string, 1: string}|null [date_from, date_to] inclusive Y-m-d
     */
    public static function resolveDateRange(
        ?string $dateFrom,
        ?string $dateTo,
        ?string $periodFrom,
        ?string $periodTo,
    ): ?array {
        $dateFrom = trim((string) $dateFrom);
        $dateTo = trim((string) $dateTo);
        $periodFrom = trim((string) $periodFrom);
        $periodTo = trim((string) $periodTo);

        if ($dateFrom !== '' || $dateTo !== '') {
            $from = $dateFrom !== '' ? CarbonImmutable::parse($dateFrom) : CarbonImmutable::parse($dateTo);
            $to = $dateTo !== '' ? CarbonImmutable::parse($dateTo) : $from;

            if ($to->lessThan($from)) {
                [$from, $to] = [$to, $from];
            }

            return [$from->toDateString(), $to->toDateString()];
        }

        if ($periodFrom !== '' || $periodTo !== '') {
            $fromMonth = $periodFrom !== ''
                ? CarbonImmutable::parse($periodFrom . '-01')->startOfMonth()
                : CarbonImmutable::parse($periodTo . '-01')->startOfMonth();
            $toMonth = $periodTo !== ''
                ? CarbonImmutable::parse($periodTo . '-01')->endOfMonth()
                : $fromMonth->endOfMonth();

            if ($toMonth->lessThan($fromMonth)) {
                [$fromMonth, $toMonth] = [$toMonth->startOfMonth(), $fromMonth->endOfMonth()];
            }

            return [$fromMonth->toDateString(), $toMonth->toDateString()];
        }

        return null;
    }

    public static function applyContactDateRange(
        EloquentBuilder|QueryBuilder $query,
        string $dateFrom,
        string $dateTo,
        string $column = 'contact_date',
    ): EloquentBuilder|QueryBuilder {
        if ($dateFrom === $dateTo) {
            return $query->whereDate($column, $dateFrom);
        }

        return $query->whereBetween($column, [$dateFrom, $dateTo]);
    }

    public static function applyDimensionFilters(
        EloquentBuilder|QueryBuilder $query,
        ?string $uen,
        ?string $channel,
        ?string $timeFrom,
        ?string $timeTo,
        string $timeColumn = 'contact_time',
    ): EloquentBuilder|QueryBuilder {
        if ($uen) {
            $query->where(function ($inner) use ($uen) {
                $inner->where('uen', $uen)
                    ->orWhereHas('client', fn ($c) => $c->where('uen', $uen));
            });
        }

        if ($channel) {
            $query->where(function ($inner) use ($channel) {
                $inner->where('channel', $channel)
                    ->orWhereHas('client', fn ($c) => $c->where('channel', $channel));
            });
        }

        if ($timeFrom) {
            $query->where($timeColumn, '>=', self::normalizeTime($timeFrom));
        }
        if ($timeTo) {
            $query->where($timeColumn, '<=', self::normalizeTime($timeTo, true));
        }

        return $query;
    }

    /** @return QueryBuilder */
    public static function previewQuery(
        string $dateFrom,
        string $dateTo,
        ?string $uen = null,
        ?string $channel = null,
        ?string $timeFrom = null,
        ?string $timeTo = null,
    ): QueryBuilder {
        $q = DB::table('management_logs as ml')
            ->join('clients as c', 'c.id', '=', 'ml.client_id')
            ->leftJoin('advisors as a', 'a.id', '=', 'ml.advisor_id')
            ->leftJoin('portfolio_documents as pd', 'pd.id', '=', 'ml.portfolio_document_id')
            ->whereNull('ml.deleted_at');

        if ($uen) {
            $q->where(function ($inner) use ($uen) {
                $inner->where('ml.uen', $uen)->orWhere('c.uen', $uen);
            });
        }

        if ($channel) {
            $q->where(function ($inner) use ($channel) {
                $inner->where('ml.channel', $channel)->orWhere('c.channel', $channel);
            });
        }

        self::applyContactDateRange($q, $dateFrom, $dateTo, 'ml.contact_date');

        if ($timeFrom) {
            $q->where('ml.contact_time', '>=', self::normalizeTime($timeFrom));
        }
        if ($timeTo) {
            $q->where('ml.contact_time', '<=', self::normalizeTime($timeTo, true));
        }

        return $q;
    }

    private static function normalizeTime(string $time, bool $end = false): string
    {
        if (strlen($time) === 5) {
            return $end ? $time . ':59' : $time . ':00';
        }

        return $time;
    }
}
