<?php

namespace App\Services\Clients;

use App\Models\Client;
use App\Models\SalesRow;
use Carbon\CarbonImmutable;

class ClientSalesRotationService
{
    /**
     * Ventas acumuladas de los últimos 12 meses para un cliente.
     * Cruza por client_id, código SAP (Código de cl) o NIT/documento.
     */
    public function salesLast12Months(Client $client, ?CarbonImmutable $asOf = null): float
    {
        $asOf ??= CarbonImmutable::now();
        $fromDate = $asOf->subMonths(12)->startOfDay()->toDateString();

        return (float) SalesRow::query()
            ->where('sale_date', '>=', $fromDate)
            ->whereHas('salesLoad', fn ($query) => $query->where('status', 'completed'))
            ->where(function ($query) use ($client): void {
                $query->where('client_id', $client->id);

                if (filled($client->code)) {
                    $query->orWhere('client_code', $client->code);
                }

                if (filled($client->document_number)) {
                    $query->orWhere('client_nit', $client->document_number);
                }
            })
            ->sum('sale_amount');
    }

    /**
     * Rotación de cartera en días: (saldo cartera / ventas 12 meses) × 360
     */
    public function rotationDays(Client $client, float $portfolioBalance, ?CarbonImmutable $asOf = null): ?float
    {
        $sales12Months = $this->salesLast12Months($client, $asOf);

        return $this->rotationDaysFromSales($portfolioBalance, $sales12Months);
    }

    public function rotationDaysFromSales(float $portfolioBalance, float $sales12Months): ?float
    {
        if ($portfolioBalance <= 0 || $sales12Months <= 0) {
            return null;
        }

        return round(($portfolioBalance / $sales12Months) * 360, 1);
    }
}
