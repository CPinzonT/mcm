<?php

namespace App\Services\Management;

use App\Models\Client;
use App\Models\ManagementLog;
use App\Models\PortfolioDocument;
use Illuminate\Support\Facades\Auth;

class ManagementLogWriter
{
    /**
     * @param  array{
     *   type: string,
     *   subject: string,
     *   description: string,
     *   result?: string|null,
     *   contact_date: string,
     *   contact_time?: string|null,
     *   follow_up_date?: string|null,
     *   promised_amount?: float|null,
     *   promised_date?: string|null,
     * }  $data
     */
    public static function createForDocument(PortfolioDocument $document, array $data): ManagementLog
    {
        $document->loadMissing('client');
        $client = $document->client ?? Client::find($document->client_id);

        $hasPromise = ! empty($data['promised_date']) || ! empty($data['promised_amount']);

        return ManagementLog::create([
            'client_id'             => $document->client_id,
            'portfolio_document_id' => $document->id,
            'advisor_id'            => $document->advisor_id ?? $client?->advisor_id,
            'user_id'               => Auth::id(),
            'type'                  => $data['type'],
            'subject'               => $data['subject'],
            'description'           => $data['description'],
            'result'                => $data['result'] ?? null,
            'contact_date'          => $data['contact_date'],
            'contact_time'          => self::normalizeTime($data['contact_time'] ?? null),
            'uen'                   => $client?->uen,
            'channel'               => $client?->channel,
            'follow_up_date'        => $data['follow_up_date'] ?? null,
            'promised_amount'       => $data['promised_amount'] ?? null,
            'promised_date'         => $data['promised_date'] ?? null,
            'status'                => $hasPromise ? 'pending' : 'open',
        ]);
    }

    private static function normalizeTime(?string $time): string
    {
        if (! $time) {
            return now()->format('H:i:s');
        }

        return match (strlen($time)) {
            5       => $time . ':00',
            8       => $time,
            default => now()->format('H:i:s'),
        };
    }
}
