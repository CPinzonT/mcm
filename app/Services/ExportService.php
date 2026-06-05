<?php

namespace App\Services;

use App\Models\Client;
use App\Models\CollectionDetail;
use App\Models\CollectionReconciliation;
use App\Models\ManagementLog;
use App\Models\PortfolioDocument;
use App\Models\ReportTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    public function exportCollectionDetails(?string $periodKey = null): StreamedResponse
    {
        $headers = [
            'Documento', 'Cliente', 'Nro. Recibo', 'Tipo Doc',
            'Importe Aplicado', 'Saldo Tras Pago', 'Fecha Pago',
            'Regional', 'Canal', 'UEN', 'Vendedor',
            'Bucket Mora', 'Estado Conciliación', 'Notas',
        ];

        $filename = 'recaudos_' . now()->format('Ymd_His') . '.xlsx';

        $query = CollectionDetail::query()->orderBy('id');

        if ($periodKey !== null && $periodKey !== '') {
            $query->where('period_key', $periodKey);
        }

        $mapper = fn ($r) => [
            $r->document_number,
            $r->client_name,
            $r->receipt_number,
            $r->document_type,
            (float) $r->amount,
            $r->pending_amount_after !== null ? (float) $r->pending_amount_after : '',
            $r->payment_date?->format('Y-m-d'),
            $r->regional,
            $r->channel,
            $r->uen,
            $r->seller_name,
            $r->bucket,
            $r->reconciliation_status,
            $r->notes,
        ];

        return $this->buildXlsxChunked($filename, $headers, $query, $mapper);
    }

    public function exportReconciliation(?string $periodKey = null): StreamedResponse
    {
        $headers = [
            'Documento', 'Cliente Recaudo', 'Cliente Cartera',
            'Valor Factura', 'Importe Aplicado', 'Saldo Cartera', 'Diferencia', 'Saldo Resultante',
            'Estado', 'Nivel Confianza', 'Conciliado En',
        ];

        $filename = 'conciliacion_' . now()->format('Ymd_His') . '.xlsx';

        $query = CollectionReconciliation::query()->orderBy('id');

        if ($periodKey !== null && $periodKey !== '') {
            $query->where('period_collection', $periodKey);
        }

        $mapper = fn ($r) => [
            $r->document_number,
            $r->client_collection,
            $r->client_portfolio,
            (float) $r->invoice_amount,
            (float) $r->applied_amount,
            (float) $r->portfolio_pending,
            (float) $r->difference,
            (float) $r->resulting_balance,
            $r->status,
            $r->confidence_level,
            $r->reconciled_at?->format('Y-m-d H:i:s'),
        ];

        return $this->buildXlsxChunked($filename, $headers, $query, $mapper);
    }

    public function exportPortfolioDocuments(string $periodKey): StreamedResponse
    {
        $headers = [
            'Periodo', 'Cuenta', 'Documento', 'Tipo', 'Cliente', 'NIT',
            'Fecha Emisión', 'Fecha Vencimiento', 'Días Mora',
            'Monto Original', 'Saldo Pendiente', 'Estado', 'Bucket', 'Riesgo',
        ];

        $rows = PortfolioDocument::query()
            ->whereHas('portfolioLoad', fn ($q) => $q->where('is_active', true)->where('status', 'completed'))
            ->where('period_date', 'like', $periodKey . '%')
            ->with('client:id,name,document_number')
            ->orderBy('id')
            ->get();

        $data = $rows->map(fn ($r) => [
            substr((string) $r->period_date, 0, 7),
            $r->account,
            $r->document_number,
            $r->document_type,
            $r->client?->name,
            $r->client?->document_number,
            $r->issue_date?->format('Y-m-d'),
            $r->due_date?->format('Y-m-d'),
            $r->days_overdue,
            (float) $r->original_amount,
            (float) $r->pending_amount,
            $r->status,
            $this->agingBucket($r->days_overdue),
            $r->risk_level,
        ])->toArray();

        $filename = "cartera_{$periodKey}_" . now()->format('Ymd_His') . '.xlsx';

        return $this->buildXlsx($filename, $headers, $data);
    }

    public function exportCommitmentActa(
        string $sessionDate,
        ?string $uen = null,
        ?string $channel = null,
        ?string $timeFrom = null,
        ?string $timeTo = null,
    ): StreamedResponse {
        $headers = [
            'Asesor', 'Cliente', 'Documento', 'Acuerdo', 'Fecha compromiso',
            'Fecha gestión', 'Hora', 'UEN', 'Canal', 'Observación',
        ];

        $filename = 'acta_compromisos_' . str_replace('-', '', $sessionDate) . '_' . now()->format('His') . '.xlsx';

        $query = ManagementLog::query()
            ->with(['advisor:id,name', 'client:id,name,uen,channel', 'portfolioDocument:id,document_number'])
            ->whereDate('contact_date', $sessionDate)
            ->orderBy('contact_time')
            ->orderBy('id');

        if ($uen) {
            $query->where(function ($q) use ($uen) {
                $q->where('uen', $uen)->orWhereHas('client', fn ($c) => $c->where('uen', $uen));
            });
        }

        if ($channel) {
            $query->where(function ($q) use ($channel) {
                $q->where('channel', $channel)->orWhereHas('client', fn ($c) => $c->where('channel', $channel));
            });
        }

        if ($timeFrom) {
            $query->where('contact_time', '>=', $this->normalizeExportTime($timeFrom));
        }
        if ($timeTo) {
            $query->where('contact_time', '<=', $this->normalizeExportTime($timeTo, true));
        }

        $mapper = function (ManagementLog $log) {
            $commitment = $log->promised_date?->format('Y-m-d')
                ?? $log->follow_up_date?->format('Y-m-d')
                ?? '';

            return [
                $log->advisor?->name ?? 'Sin asignar',
                $log->client?->name ?? '',
                $log->portfolioDocument?->document_number ?? '',
                trim($log->getTypeLabel() . ': ' . $log->subject),
                $commitment,
                $log->contact_date?->format('Y-m-d') ?? '',
                $log->contact_time ? substr((string) $log->contact_time, 0, 8) : '',
                $log->uen ?? $log->client?->uen ?? '',
                $log->channel ?? $log->client?->channel ?? '',
                $log->description,
            ];
        };

        return $this->buildXlsxChunked($filename, $headers, $query, $mapper);
    }

    private function normalizeExportTime(string $time, bool $end = false): string
    {
        if (strlen($time) === 5) {
            return $end ? $time . ':59' : $time . ':00';
        }

        return $time;
    }

    private function buildXlsxChunked(
        string $filename,
        array $headers,
        \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query,
        callable $mapper,
        int $chunkSize = 1000,
    ): StreamedResponse
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'xlsxexp_') . '.xlsx';

        $writer = new XlsxWriter();
        $writer->openToFile($tmpPath);

        $writer->addRow(Row::fromValues($headers));

        $query->chunk($chunkSize, function ($records) use ($writer, $mapper) {
            foreach ($records as $record) {
                $writer->addRow(Row::fromValues(($mapper)($record)));
            }
        });

        $writer->close();

        return response()->streamDownload(function () use ($tmpPath): void {
            $handle = fopen($tmpPath, 'rb');
            while (! feof($handle)) {
                echo fread($handle, 8192);
                flush();
            }
            fclose($handle);
            @unlink($tmpPath);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function buildXlsx(string $filename, array $headers, array $data, array $options = []): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reporte');

        $columnCount = max(1, count($headers));
        $lastColumn = Coordinate::stringFromColumnIndex($columnCount);
        $primaryColor = $this->excelColor($options['primary_color'] ?? null, '1E3A5F');
        $accentColor = $this->excelColor($options['accent_color'] ?? null, '2563EB');

        $showHeader = (bool) ($options['show_header'] ?? false);
        $rowPointer = 1;

        if ($showHeader) {
            $title = trim((string) ($options['title'] ?? ''));
            $subtitle = trim((string) ($options['subtitle'] ?? ''));
            $period = trim((string) ($options['period'] ?? ''));
            $logoPath = $options['logo_path'] ?? null;

            $titleStartCol = 'A';

            if ($logoPath && $columnCount > 1) {
                $drawing = new Drawing();
                $drawing->setName('Logo');
                $drawing->setDescription('Logo del informe');
                $drawing->setPath($logoPath);
                $drawing->setHeight(44);
                $drawing->setCoordinates('A1');
                $drawing->setOffsetX(6);
                $drawing->setOffsetY(4);
                $drawing->setWorksheet($sheet);

                $titleStartCol = 'B';
                $sheet->getRowDimension(1)->setRowHeight(36);
            }

            if ($title !== '') {
                if ($titleStartCol !== $lastColumn) {
                    $sheet->mergeCells("{$titleStartCol}{$rowPointer}:{$lastColumn}{$rowPointer}");
                }
                $sheet->setCellValue("{$titleStartCol}{$rowPointer}", $title);
                $sheet->getStyle("{$titleStartCol}{$rowPointer}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => $primaryColor]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $rowPointer++;
            }

            if ($subtitle !== '') {
                $sheet->mergeCells("A{$rowPointer}:{$lastColumn}{$rowPointer}");
                $sheet->setCellValue("A{$rowPointer}", $subtitle);
                $sheet->getStyle("A{$rowPointer}")->applyFromArray([
                    'font' => ['italic' => true, 'size' => 11, 'color' => ['argb' => 'FF475569']],
                ]);
                $rowPointer++;
            }

            if ($period !== '') {
                $sheet->mergeCells("A{$rowPointer}:{$lastColumn}{$rowPointer}");
                $sheet->setCellValue("A{$rowPointer}", 'Periodo: ' . $period);
                $sheet->getStyle("A{$rowPointer}")->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['argb' => 'FF64748B']],
                ]);
                $rowPointer++;
            }

            $rowPointer++;
        }

        $headerRow = $rowPointer;

        foreach ($headers as $col => $header) {
            $cell = $sheet->getCellByColumnAndRow($col + 1, $headerRow);
            $cell->setValue($header);
        }

        $sheet->getStyle("A{$headerRow}:{$lastColumn}{$headerRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => $primaryColor],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $dataStartRow = $headerRow + 1;

        foreach ($data as $rowIdx => $row) {
            foreach ($row as $colIdx => $value) {
                $sheet->getCellByColumnAndRow($colIdx + 1, $rowIdx + $dataStartRow)->setValue($value);
            }
        }

        $lastDataRow = max($headerRow, $dataStartRow + count($data) - 1);

        if (count($data) > 0) {
            $sheet->getStyle("A{$headerRow}:{$lastColumn}{$lastDataRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'FFD8E1EE'],
                    ],
                ],
            ]);

            for ($rowNumber = $dataStartRow; $rowNumber <= $lastDataRow; $rowNumber++) {
                if (($rowNumber - $dataStartRow) % 2 === 1) {
                    $sheet->getStyle("A{$rowNumber}:{$lastColumn}{$rowNumber}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('FFF8FAFC');
                }
            }
        }

        $columnFormats = $options['column_formats'] ?? [];
        $columnAlignments = $options['column_alignments'] ?? [];
        $columnWidths = $options['column_widths'] ?? [];

        foreach (range(1, $columnCount) as $index) {
            $letter = Coordinate::stringFromColumnIndex($index);
            $format = $columnFormats[$index - 1] ?? 'text';
            $align = $columnAlignments[$index - 1] ?? '';
            $width = $columnWidths[$index - 1] ?? null;

            if (count($data) > 0) {
                $range = "{$letter}{$dataStartRow}:{$letter}{$lastDataRow}";

                if ($format === 'currency') {
                    $sheet->getStyle($range)->getNumberFormat()->setFormatCode('#,##0.00');
                } elseif ($format === 'integer') {
                    $sheet->getStyle($range)->getNumberFormat()->setFormatCode('0');
                } elseif ($format === 'percentage') {
                    $sheet->getStyle($range)->getNumberFormat()->setFormatCode('0.00%');
                } elseif ($format === 'date') {
                    $sheet->getStyle($range)->getNumberFormat()->setFormatCode('yyyy-mm-dd');
                }

                $sheet->getStyle($range)->getAlignment()->setHorizontal(
                    match ($align !== '' ? $align : $format) {
                        'right', 'currency', 'integer', 'percentage' => Alignment::HORIZONTAL_RIGHT,
                        'center', 'date' => Alignment::HORIZONTAL_CENTER,
                        default => Alignment::HORIZONTAL_LEFT,
                    }
                );
            }

            if (is_string($width) && is_numeric($width)) {
                $sheet->getColumnDimension($letter)->setWidth((float) $width);
            } elseif (is_numeric($width)) {
                $sheet->getColumnDimension($letter)->setWidth((float) $width);
            } else {
                $sheet->getColumnDimension($letter)->setAutoSize(true);
            }
        }

        $sheet->freezePane('A' . ($headerRow + 1));
        $sheet->setAutoFilter("A{$headerRow}:{$lastColumn}{$headerRow}");

        if (!empty($options['show_footer']) || !empty($options['show_page_numbers'])) {
            $footerLeft = '';
            $footerRight = '';

            if (!empty($options['show_footer']) && !empty($options['footer_text'])) {
                $footerLeft = str_replace('&', '&&', (string) $options['footer_text']);
            }

            if (!empty($options['show_page_numbers'])) {
                $footerRight = 'Página &P de &N';
            }

            $sheet->getHeaderFooter()->setOddFooter('&L' . $footerLeft . '&R' . $footerRight);
        }

        $sheet->getStyle("A{$headerRow}:{$lastColumn}{$headerRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
        $sheet->getStyle("A{$headerRow}:{$lastColumn}{$headerRow}")->getBorders()->getBottom()->getColor()->setARGB($accentColor);
        $sheet->getDefaultRowDimension()->setRowHeight(18);

        return response()->streamDownload(function () use ($spreadsheet): void {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function exportAgingReport(string $periodKey, ?int $templateId = null, ?int $clientId = null): StreamedResponse
    {
        $template = $this->resolveAgingTemplate($templateId);
        $columns  = $this->resolveAgingColumns($template, $clientId);
        $headers  = array_map(fn (array $column): string => $column['label'], $columns);

        $client = $clientId
            ? Client::query()->find($clientId, ['id', 'name', 'document_number'])
            : null;

        $query = PortfolioDocument::query()
            ->whereIn('status', PortfolioDocument::BALANCE_STATUSES)
            ->whereNull('deleted_at')
            ->with(['client:id,name,document_number,region,channel,uen', 'advisor:id,name'])
            ->orderByRaw('CASE
                WHEN days_overdue <= 0 THEN 0
                WHEN days_overdue <= 30 THEN 1
                WHEN days_overdue <= 60 THEN 2
                WHEN days_overdue <= 90 THEN 3
                WHEN days_overdue <= 120 THEN 4
                WHEN days_overdue <= 180 THEN 5
                WHEN days_overdue <= 360 THEN 6
                ELSE 7
            END, document_number');

        if ($clientId) {
            $loadId = DB::table('portfolio_loads')
                ->where('is_active', true)
                ->where('status', 'completed')
                ->orderByDesc('period_date')
                ->orderByDesc('version')
                ->value('id');

            if ($loadId) {
                $query->where('portfolio_load_id', (int) $loadId);
                $cut = DB::table('portfolio_loads')->where('id', $loadId)->value('period_date');
                if ($cut) {
                    $query->whereDate('period_date', $cut);
                }
            } else {
                $query->whereRaw('1 = 0');
            }

            $query->where('client_id', $clientId);
        } else {
            $query->whereHas('portfolioLoad', fn ($q) => $q->where('is_active', true)->where('status', 'completed'))
                ->where('period_date', 'like', $periodKey . '%')
                ->whereIn('status', ['active', 'partial', 'in_process']);
        }

        if ($template && $template->type === 'client') {
            if ($template->client_id) {
                $query->where('client_id', $template->client_id);
            } else {
                // Misconfigured client template should never export global data.
                $query->whereRaw('1 = 0');
            }
        }

        $rows = $query->get();

        $data = $rows->map(function (PortfolioDocument $document) use ($columns, $periodKey): array {
            return collect($columns)
                ->map(function (array $column) use ($document, $periodKey) {
                    $rawValue = $this->resolveTemplateFieldValue($document, $column['field_key'], $periodKey);
                    return $this->formatTemplateValue($rawValue, $column['format'] ?? 'text');
                })
                ->values()
                ->all();
        })->toArray();

        $templateSuffix = $template?->slug ? '_' . $template->slug : '';
        if ($client) {
            $slug = Str::slug($client->name) ?: 'cliente';
            $nit  = preg_replace('/\D+/', '', (string) $client->document_number) ?: 'sin-nit';
            $filename = "cartera_{$slug}_{$nit}_{$periodKey}_" . now()->format('Ymd_His') . '.xlsx';
        } else {
            $filename = "aging_{$periodKey}{$templateSuffix}_" . now()->format('Ymd_His') . '.xlsx';
        }

        $branding = $template?->brandingProfile;

        $reportTitle = $template?->title ?: 'Reporte Aging de Cartera';
        $reportSubtitle = $template?->subtitle;
        if ($client) {
            $reportTitle = $client->name;
            $reportSubtitle = 'NIT ' . ($client->document_number ?: '—');
        }

        return $this->buildXlsx($filename, $headers, $data, [
            'show_header' => $client ? true : (bool) ($template?->show_header ?? true),
            'title' => $reportTitle,
            'subtitle' => $reportSubtitle,
            'period' => $periodKey,
            'show_footer' => (bool) ($template?->show_footer ?? false),
            'footer_text' => $branding?->footer_text,
            'show_page_numbers' => (bool) ($template?->show_page_numbers ?? false),
            'primary_color' => $branding?->primary_color,
            'accent_color' => $branding?->accent_color,
            'logo_path' => $this->resolveLogoAbsolutePath(
                (bool) ($template?->show_logo ?? false),
                $branding?->logo_path,
            ),
            'column_formats' => array_map(fn (array $column): string => $column['format'] ?? 'text', $columns),
            'column_alignments' => array_map(fn (array $column): string => $column['align'] ?? '', $columns),
            'column_widths' => array_map(fn (array $column): ?string => $column['width'] ?? null, $columns),
        ]);
    }

    private function resolveAgingTemplate(?int $templateId): ?ReportTemplate
    {
        if (!$templateId) {
            return null;
        }

        return ReportTemplate::query()
            ->where('active', true)
            ->with([
                'columns' => fn ($query) => $query->where('visible', true)->orderBy('order'),
                'brandingProfile',
            ])
            ->find($templateId);
    }

    private function resolveAgingColumns(?ReportTemplate $template, ?int $clientId = null): array
    {
        if ($template && $template->columns->isNotEmpty()) {
            $columns = $template->columns
                ->map(fn ($column): array => [
                    'field_key' => $column->field_key,
                    'label'     => $column->label ?: $column->field_key,
                    'format'    => $column->format ?: 'text',
                    'align'     => $column->align ?: '',
                    'width'     => $column->width,
                ])
                ->values()
                ->all();
        } elseif ($clientId) {
            $columns = [
                ['field_key' => 'document_number', 'label' => 'Documento', 'format' => 'text', 'align' => 'left', 'width' => '18'],
                ['field_key' => 'document_type', 'label' => 'Tipo', 'format' => 'text', 'align' => 'left', 'width' => '14'],
                ['field_key' => 'advisor.name', 'label' => 'Asesor', 'format' => 'text', 'align' => 'left', 'width' => '24'],
                ['field_key' => 'issue_date', 'label' => 'Fecha emisión', 'format' => 'date', 'align' => 'center', 'width' => '14'],
                ['field_key' => 'due_date', 'label' => 'Fecha vencimiento', 'format' => 'date', 'align' => 'center', 'width' => '14'],
                ['field_key' => 'days_overdue', 'label' => 'Días de mora', 'format' => 'integer', 'align' => 'right', 'width' => '12'],
                ['field_key' => 'original_amount', 'label' => 'Valor original', 'format' => 'currency', 'align' => 'right', 'width' => '16'],
                ['field_key' => 'pending_amount', 'label' => 'Saldo pendiente', 'format' => 'currency', 'align' => 'right', 'width' => '16'],
                ['field_key' => 'status', 'label' => 'Estado', 'format' => 'text', 'align' => 'left', 'width' => '12'],
                ['field_key' => 'aging_bucket', 'label' => 'Rango de mora', 'format' => 'text', 'align' => 'left', 'width' => '14'],
                ['field_key' => 'risk_level', 'label' => 'Riesgo', 'format' => 'text', 'align' => 'left', 'width' => '12'],
                ['field_key' => 'client.uen', 'label' => 'UEN', 'format' => 'text', 'align' => 'left', 'width' => '12'],
                ['field_key' => 'client.channel', 'label' => 'Canal', 'format' => 'text', 'align' => 'left', 'width' => '12'],
            ];
        } else {
            $columns = [
                ['field_key' => 'document_number', 'label' => 'Factura', 'format' => 'text', 'align' => 'left', 'width' => null],
                ['field_key' => 'document_type', 'label' => 'Tipo de documento', 'format' => 'text', 'align' => 'left', 'width' => null],
                ['field_key' => 'due_date', 'label' => 'Fecha de vencimiento', 'format' => 'date', 'align' => 'center', 'width' => null],
                ['field_key' => 'days_overdue', 'label' => 'Días de mora', 'format' => 'integer', 'align' => 'right', 'width' => null],
                ['field_key' => 'original_amount', 'label' => 'Valor original', 'format' => 'currency', 'align' => 'right', 'width' => null],
                ['field_key' => 'pending_amount', 'label' => 'Saldo', 'format' => 'currency', 'align' => 'right', 'width' => null],
                ['field_key' => 'aging_bucket', 'label' => 'Rango de mora', 'format' => 'text', 'align' => 'left', 'width' => null],
                ['field_key' => 'client.uen', 'label' => 'UEN', 'format' => 'text', 'align' => 'left', 'width' => null],
                ['field_key' => 'client.channel', 'label' => 'Canal', 'format' => 'text', 'align' => 'left', 'width' => null],
            ];
        }

        if ($clientId) {
            $columns = $this->ensureAdvisorColumn($columns);
        }

        return $columns;
    }

    /**
     * @param  array<int, array{field_key: string, label: string, format: string, align: string, width: string|null}>  $columns
     * @return array<int, array{field_key: string, label: string, format: string, align: string, width: string|null}>
     */
    private function ensureAdvisorColumn(array $columns): array
    {
        foreach ($columns as $column) {
            if (($column['field_key'] ?? '') === 'advisor.name') {
                return $columns;
            }
        }

        $advisorCol = [
            'field_key' => 'advisor.name',
            'label'     => 'Asesor',
            'format'    => 'text',
            'align'     => 'left',
            'width'     => '24',
        ];

        $insertAt = 2;
        foreach ($columns as $i => $column) {
            if (($column['field_key'] ?? '') === 'document_type') {
                $insertAt = $i + 1;
                break;
            }
        }

        array_splice($columns, $insertAt, 0, [$advisorCol]);

        return $columns;
    }

    private function resolveTemplateFieldValue(PortfolioDocument $document, string $fieldKey, string $periodKey): mixed
    {
        return match ($fieldKey) {
            'aging_bucket' => $this->agingBucket((int) $document->days_overdue),
            'period_key'   => $periodKey,
            'period_date'  => $document->period_date?->format('Y-m-d'),
            'risk_level'   => $this->riskLabel((string) $document->risk_level),
            'status'       => $this->statusLabel((string) $document->status),
            'advisor.name' => $document->advisor?->name ?: 'Sin asesor',
            default        => data_get($document, $fieldKey),
        };
    }

    private function formatTemplateValue(mixed $value, string $format): mixed
    {
        if ($value === null) {
            return '';
        }

        return match ($format) {
            'currency' => is_numeric($value) ? (float) $value : $value,
            'integer' => is_numeric($value) ? (int) $value : $value,
            'date' => $this->normalizeDateValue($value),
            'percentage' => is_numeric($value) ? (float) $value : $value,
            default => is_scalar($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE),
        };
    }

    private function normalizeDateValue(mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_string($value) && strlen($value) >= 10) {
            return substr($value, 0, 10);
        }

        return $value;
    }

    private function resolveLogoAbsolutePath(bool $showLogo, ?string $logoPath): ?string
    {
        if (!$showLogo || !$logoPath) {
            return null;
        }

        if (str_starts_with($logoPath, 'http://') || str_starts_with($logoPath, 'https://')) {
            return null;
        }

        $relative = str_starts_with($logoPath, '/storage/')
            ? substr($logoPath, strlen('/storage/'))
            : ltrim($logoPath, '/');

        $absolutePath = storage_path('app/public/' . ltrim($relative, '/'));

        return is_file($absolutePath) ? $absolutePath : null;
    }

    private function excelColor(?string $hex, string $fallback): string
    {
        $value = strtoupper(ltrim((string) ($hex ?: ''), '#'));

        if (! preg_match('/^[0-9A-F]{6}$/', $value)) {
            $value = strtoupper(ltrim($fallback, '#'));
        }

        return 'FF' . $value;
    }

    private function riskLabel(string $riskLevel): string
    {
        return match ($riskLevel) {
            'normal' => 'Normal',
            'low' => 'Bajo',
            'medium' => 'Medio',
            'high' => 'Alto',
            'critical' => 'Crítico',
            default => $riskLevel,
        };
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'active' => 'Activo',
            'partial' => 'Parcial',
            'paid' => 'Pagado',
            'written_off' => 'Castigado',
            'in_process' => 'En Proceso',
            default => $status,
        };
    }

    private function agingBucket(int $days): string
    {
        return match (true) {
            $days <= 0  => 'Sin vencer',
            $days <= 30 => '1-30',
            $days <= 60 => '31-60',
            $days <= 90 => '61-90',
            default     => '>90',
        };
    }
}
