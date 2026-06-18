<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CollectionLoad;
use App\Models\PortfolioLoad;
use App\Models\SalesLoad;
use App\Services\Loads\Support\PortfolioLoadTemplate;
use Illuminate\Support\Facades\Storage;

class LoadDownloadController extends Controller
{
    public function portfolioTemplate()
    {
        $headers = PortfolioLoadTemplate::headers();

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

    public function budgetTemplate()
    {
        $headers = [
            'NOMBRE CLIENTE',
            'REGIONAL',
            'DESC CANAL',
            'VENDEDOR',
            'TIPO TRANSACCION',
            'NO FACTURA',
            'FECHA FACTURA',
            'FECHA VENCIMIENTO',
            'DIAS VENCIMIENTO',
            'MONTO INICIAL',
            'SALDO DEBIDO',
            '1-90',
            '>90',
            'SIN VENCER',
            'ROTACION',
            'PPTO',
            'RECAUDO',
            'CATEGORIAS',
            'Fecha de aplicación',
        ];

        return response()->streamDownload(function () use ($headers): void {
            $stream = fopen('php://output', 'wb');
            fputcsv($stream, $headers);
            fclose($stream);
        }, 'plantilla-carga-presupuesto.csv', ['Content-Type' => 'text/csv']);
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

    public function salesTemplate()
    {
        $headers = [
            'Source.Name',
            'Tipo factura',
            'Cliente',
            'Cod_Product',
            'Producto',
            'Canal',
            'Regional',
            'Vendedor',
            'Marca Corpo',
            'Negocio',
            'Subnegocio',
            'Linea',
            'Sublinea',
            'Mes',
            'Proveedor',
            'periodo',
            'Año',
            'Costo',
            'UB',
            'Unidades',
            'Venta',
            'Fecha',
            'Código de cl',
            'Cod Proveed',
            'Origen',
            'Descripción',
            'Dir Destino',
            'No. Factura',
            'Lider',
            'UEN',
            'Gestión Com',
        ];

        return response()->streamDownload(function () use ($headers): void {
            $stream = fopen('php://output', 'wb');
            fputcsv($stream, $headers);
            fclose($stream);
        }, 'plantilla-carga-ventas.csv', ['Content-Type' => 'text/csv']);
    }

    public function salesErrors(SalesLoad $salesLoad)
    {
        $this->authorize('view', $salesLoad);

        return response()->streamDownload(function () use ($salesLoad): void {
            $stream = fopen('php://output', 'wb');
            fputcsv($stream, ['fila', 'campo', 'codigo', 'mensaje']);

            foreach ($salesLoad->error_log ?? [] as $error) {
                fputcsv($stream, [
                    $error['row_number'] ?? '',
                    $error['field'] ?? '',
                    $error['error_code'] ?? '',
                    $error['message'] ?? '',
                ]);
            }

            fclose($stream);
        }, "errores-ventas-{$salesLoad->reference}.csv", ['Content-Type' => 'text/csv']);
    }

    public function salesSource(SalesLoad $salesLoad)
    {
        $this->authorize('view', $salesLoad);

        return Storage::disk($salesLoad->disk)->download(
            $salesLoad->path,
            $salesLoad->original_filename ?: basename($salesLoad->path),
        );
    }

    public function clientContactsTemplate()
    {
        $headers = [
            'NIT',
            'Nombre SN',
            'Fecha de creación',
            'Canal',
            'Territorio',
            'Código de cc',
            'Límite de crédito',
            'Teléfono móvil',
            'Correo electrónico',
            'Ciudad (MM)',
            'Dirección (MM)',
        ];

        return response()->streamDownload(function () use ($headers): void {
            $stream = fopen('php://output', 'wb');
            fputcsv($stream, $headers);
            fclose($stream);
        }, 'plantilla-maestro-contactos-clientes.csv', ['Content-Type' => 'text/csv']);
    }
}
