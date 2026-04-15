<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CollectionLoad;
use App\Models\PortfolioLoad;
use Illuminate\Support\Facades\Storage;

class LoadDownloadController extends Controller
{
    public function portfolioTemplate()
    {
        $headers = [
            '#',
            'cuenta',
            'cliente',
            'nit',
            'direccion',
            'contacto',
            'telefono',
            'canal',
            'uens',
            'empleado_de_ventas',
            'regional',
            'nro_documento',
            'nro_ref_de_cliente',
            'tipo',
            'fecha_contabilizacion',
            'fecha_activacion',
            'fecha_vencimiento',
            'valor_documento',
            'saldo_pendiente',
            'moneda',
            'dias_vencido',
            'actual',
            '1_30_dias',
            '31_60_dias',
            '61_90_dias',
            '91_180_dias',
            '181_360_dias',
            '361_dias',
        ];

        return response()->streamDownload(function () use ($headers): void {
            $stream = fopen('php://output', 'wb');
            fputcsv($stream, $headers);
            fclose($stream);
        }, 'plantilla-carga-cartera.csv', ['Content-Type' => 'text/csv']);
    }

    public function collectionTemplate()
    {
        $headers = [
            'documento',
            'valor_pagado',
            'fecha_pago',
            'nro_recibo',
            'cliente',
            'vendedor',
            'periodo',
            'observacion',
        ];

        return response()->streamDownload(function () use ($headers): void {
            $stream = fopen('php://output', 'wb');
            fputcsv($stream, $headers);
            fclose($stream);
        }, 'plantilla-carga-recaudos.csv', ['Content-Type' => 'text/csv']);
    }

    public function portfolioErrors(PortfolioLoad $portfolioLoad)
    {
        $this->authorize('view', $portfolioLoad);

        return response()->streamDownload(function () use ($portfolioLoad): void {
            $stream = fopen('php://output', 'wb');
            fputcsv($stream, ['fila', 'campo', 'codigo', 'mensaje']);

            foreach ($portfolioLoad->errors()->orderBy('row_number')->cursor() as $error) {
                fputcsv($stream, [
                    $error->row_number,
                    $error->field,
                    $error->error_code,
                    $error->message,
                ]);
            }

            fclose($stream);
        }, "errores-cartera-{$portfolioLoad->reference}.csv", ['Content-Type' => 'text/csv']);
    }

    public function collectionErrors(CollectionLoad $collectionLoad)
    {
        $this->authorize('view', $collectionLoad);

        return response()->streamDownload(function () use ($collectionLoad): void {
            $stream = fopen('php://output', 'wb');
            fputcsv($stream, ['fila', 'campo', 'codigo', 'mensaje']);

            foreach ($collectionLoad->errors()->orderBy('row_number')->cursor() as $error) {
                fputcsv($stream, [
                    $error->row_number,
                    $error->field,
                    $error->error_code,
                    $error->message,
                ]);
            }

            fclose($stream);
        }, "errores-recaudos-{$collectionLoad->reference}.csv", ['Content-Type' => 'text/csv']);
    }

    public function portfolioSource(PortfolioLoad $portfolioLoad)
    {
        $this->authorize('view', $portfolioLoad);

        return Storage::disk($portfolioLoad->disk)->download(
            $portfolioLoad->path,
            $portfolioLoad->original_filename ?: basename($portfolioLoad->path),
        );
    }

    public function collectionSource(CollectionLoad $collectionLoad)
    {
        $this->authorize('view', $collectionLoad);

        return Storage::disk($collectionLoad->disk)->download(
            $collectionLoad->path,
            $collectionLoad->original_filename ?: basename($collectionLoad->path),
        );
    }
}
