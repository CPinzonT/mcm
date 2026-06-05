<?php

namespace App\Filament\Pages;

use App\Models\Client;
use App\Models\ManagementLog;
use App\Models\PortfolioDocument;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

class ReportsPage extends Page
{
    protected string $view = 'filament.pages.reports-page';

    protected static string|\BackedEnum|null $navigationIcon  = 'heroicon-o-chart-bar';
    protected static string|\UnitEnum|null   $navigationGroup = 'Inteligencia';
    protected static ?string $navigationLabel = 'Reportes';
    protected static ?string $title           = 'Reportes';
    protected static ?int    $navigationSort  = 1;

    public string $reportType = '';
    public string $periodFrom = '';
    public string $periodTo   = '';
    public string $uen        = '';
    public string $channel    = '';
    public string $sessionDate = '';
    public string $timeFrom   = '';
    public string $timeTo     = '';

    public ?array $rows    = null;
    public array  $columns = [];
    public array  $summary = ['total_rows' => 0, 'total_amount' => 0];

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return null;
    }

    #[Computed]
    public function uenOptions(): array
    {
        return DB::table('clients')
            ->whereNotNull('uen')->where('uen', '!=', '')
            ->select('uen')->distinct()->orderBy('uen')
            ->pluck('uen', 'uen')->toArray();
    }

    #[Computed]
    public function channelOptions(): array
    {
        return DB::table('clients')
            ->whereNotNull('channel')->where('channel', '!=', '')
            ->select('channel')->distinct()->orderBy('channel')
            ->pluck('channel', 'channel')->toArray();
    }

    public function exportActaUrl(): ?string
    {
        if ($this->reportType !== 'acta_compromisos' || ! $this->sessionDate) {
            return null;
        }

        return route('admin.exports.commitment-acta', array_filter([
            'uen'          => $this->uen ?: null,
            'channel'      => $this->channel ?: null,
            'session_date' => $this->sessionDate,
            'time_from'    => $this->timeFrom ?: null,
            'time_to'      => $this->timeTo ?: null,
        ]));
    }

    public function generateReport(): void
    {
        if (!$this->reportType) {
            Notification::make()->title('Selecciona un tipo de reporte')->warning()->send();
            return;
        }

        if ($this->reportType === 'acta_compromisos' && ! $this->sessionDate) {
            Notification::make()
                ->title('Indica la fecha de la sesión')
                ->body('La acta de compromisos filtra por fecha y rango horario de gestión.')
                ->warning()
                ->send();
            return;
        }

        [$this->columns, $this->rows, $this->summary] = match ($this->reportType) {
            'cartera_regional'     => $this->reportCarteraRegional(),
            'cartera_canal'        => $this->reportCarteraCanal(),
            'cartera_gestor'       => $this->reportCarteraGestor(),
            'promesas_pendientes'  => $this->reportPromesasPendientes(),
            'promesas_incumplidas' => $this->reportPromesasIncumplidas(),
            'gestiones_gestor'     => $this->reportGestionesGestor(),
            'acta_compromisos'     => $this->reportActaCompromisos(),
            'analisis_vencimiento' => $this->reportAnalisisVencimiento(),
            default                => [[], [], ['total_rows' => 0, 'total_amount' => 0]],
        };
    }

    private function periodFilter(\Illuminate\Database\Query\Builder $q, string $alias = 'pd'): void
    {
        if ($this->periodFrom) {
            $q->where("{$alias}.period_date", '>=', $this->periodFrom . '-01');
        }
        if ($this->periodTo) {
            $q->where("{$alias}.period_date", '<=', $this->periodTo . '-28');
        }
        if ($this->uen) {
            $q->where('c.uen', $this->uen);
        }
    }

    private function basePortfolioQuery(): \Illuminate\Database\Query\Builder
    {
        $q = DB::table('portfolio_documents as pd')
            ->join('clients as c', 'c.id', '=', 'pd.client_id')
            ->whereNull('pd.deleted_at');

        $this->periodFilter($q);

        return $q;
    }

    private function reportCarteraRegional(): array
    {
        $columns = [
            ['key' => 'region',         'label' => 'Regional'],
            ['key' => 'clientes',       'label' => 'Clientes'],
            ['key' => 'documentos',     'label' => 'Documentos'],
            ['key' => 'saldo_total',    'label' => 'Saldo total'],
            ['key' => 'saldo_vencido',  'label' => 'Saldo vencido'],
        ];

        $rows = $this->basePortfolioQuery()
            ->select([
                'c.region',
                DB::raw('COUNT(DISTINCT c.id) as clientes'),
                DB::raw('COUNT(pd.id) as documentos'),
                DB::raw('SUM(pd.pending_amount) as saldo_total'),
                DB::raw('SUM(CASE WHEN pd.days_overdue > 0 THEN pd.pending_amount ELSE 0 END) as saldo_vencido'),
            ])
            ->groupBy('c.region')
            ->orderByDesc('saldo_total')
            ->get()->toArray();

        $total = array_sum(array_column((array) $rows, 'saldo_total'));

        return [$columns, $rows, ['total_rows' => count($rows), 'total_amount' => $total]];
    }

    private function reportCarteraCanal(): array
    {
        $columns = [
            ['key' => 'channel',        'label' => 'Canal'],
            ['key' => 'clientes',       'label' => 'Clientes'],
            ['key' => 'documentos',     'label' => 'Documentos'],
            ['key' => 'saldo_total',    'label' => 'Saldo total'],
            ['key' => 'saldo_vencido',  'label' => 'Saldo vencido'],
        ];

        $rows = $this->basePortfolioQuery()
            ->select([
                'c.channel',
                DB::raw('COUNT(DISTINCT c.id) as clientes'),
                DB::raw('COUNT(pd.id) as documentos'),
                DB::raw('SUM(pd.pending_amount) as saldo_total'),
                DB::raw('SUM(CASE WHEN pd.days_overdue > 0 THEN pd.pending_amount ELSE 0 END) as saldo_vencido'),
            ])
            ->groupBy('c.channel')
            ->orderByDesc('saldo_total')
            ->get()->toArray();

        $total = array_sum(array_column((array) $rows, 'saldo_total'));

        return [$columns, $rows, ['total_rows' => count($rows), 'total_amount' => $total]];
    }

    private function reportCarteraGestor(): array
    {
        $columns = [
            ['key' => 'advisor',        'label' => 'Asesor'],
            ['key' => 'clientes',       'label' => 'Clientes'],
            ['key' => 'documentos',     'label' => 'Documentos'],
            ['key' => 'saldo_total',    'label' => 'Saldo total'],
            ['key' => 'saldo_vencido',  'label' => 'Saldo vencido'],
        ];

        $rows = $this->basePortfolioQuery()
            ->leftJoin('advisors as a', 'a.id', '=', 'pd.advisor_id')
            ->select([
                DB::raw('COALESCE(a.name, "Sin asignar") as advisor'),
                DB::raw('COUNT(DISTINCT c.id) as clientes'),
                DB::raw('COUNT(pd.id) as documentos'),
                DB::raw('SUM(pd.pending_amount) as saldo_total'),
                DB::raw('SUM(CASE WHEN pd.days_overdue > 0 THEN pd.pending_amount ELSE 0 END) as saldo_vencido'),
            ])
            ->groupBy('pd.advisor_id', 'a.name')
            ->orderByDesc('saldo_total')
            ->get()->toArray();

        $total = array_sum(array_column((array) $rows, 'saldo_total'));

        return [$columns, $rows, ['total_rows' => count($rows), 'total_amount' => $total]];
    }

    private function reportPromesasPendientes(): array
    {
        $columns = [
            ['key' => 'client',          'label' => 'Cliente'],
            ['key' => 'advisor',         'label' => 'Asesor'],
            ['key' => 'promised_date',   'label' => 'Fecha promesa'],
            ['key' => 'promised_amount', 'label' => 'Monto prometido'],
            ['key' => 'contact_date',    'label' => 'Fecha gestión'],
        ];

        $q = DB::table('management_logs as ml')
            ->join('clients as c', 'c.id', '=', 'ml.client_id')
            ->leftJoin('advisors as a', 'a.id', '=', 'ml.advisor_id')
            ->whereNull('ml.deleted_at')
            ->where('ml.status', 'pending')
            ->whereNotNull('ml.promised_date')
            ->where('ml.promised_date', '>=', now()->toDateString());

        if ($this->uen) {
            $q->where('c.uen', $this->uen);
        }
        if ($this->periodFrom) {
            $q->where('ml.promised_date', '>=', $this->periodFrom . '-01');
        }
        if ($this->periodTo) {
            $q->where('ml.promised_date', '<=', $this->periodTo . '-28');
        }

        $rows = $q->select([
                'c.name as client',
                DB::raw('COALESCE(a.name, "Sin asignar") as advisor'),
                'ml.promised_date',
                'ml.promised_amount',
                'ml.contact_date',
            ])
            ->orderBy('ml.promised_date')
            ->get()->toArray();

        $total = array_sum(array_column((array) $rows, 'promised_amount'));

        return [$columns, $rows, ['total_rows' => count($rows), 'total_amount' => $total]];
    }

    private function reportPromesasIncumplidas(): array
    {
        $columns = [
            ['key' => 'client',          'label' => 'Cliente'],
            ['key' => 'advisor',         'label' => 'Asesor'],
            ['key' => 'promised_date',   'label' => 'Fecha promesa'],
            ['key' => 'promised_amount', 'label' => 'Monto prometido'],
            ['key' => 'dias_vencida',    'label' => 'Días vencida'],
        ];

        $q = DB::table('management_logs as ml')
            ->join('clients as c', 'c.id', '=', 'ml.client_id')
            ->leftJoin('advisors as a', 'a.id', '=', 'ml.advisor_id')
            ->whereNull('ml.deleted_at')
            ->where('ml.status', 'broken')
            ->whereNotNull('ml.promised_date');

        if ($this->uen) {
            $q->where('c.uen', $this->uen);
        }
        if ($this->periodFrom) {
            $q->where('ml.promised_date', '>=', $this->periodFrom . '-01');
        }
        if ($this->periodTo) {
            $q->where('ml.promised_date', '<=', $this->periodTo . '-28');
        }

        $rows = $q->select([
                'c.name as client',
                DB::raw('COALESCE(a.name, "Sin asignar") as advisor'),
                'ml.promised_date',
                'ml.promised_amount',
                DB::raw('DATEDIFF(CURDATE(), ml.promised_date) as dias_vencida'),
            ])
            ->orderByDesc('dias_vencida')
            ->get()->toArray();

        $total = array_sum(array_column((array) $rows, 'promised_amount'));

        return [$columns, $rows, ['total_rows' => count($rows), 'total_amount' => $total]];
    }

    private function reportGestionesGestor(): array
    {
        $columns = [
            ['key' => 'advisor',     'label' => 'Asesor'],
            ['key' => 'total',       'label' => 'Total gestiones'],
            ['key' => 'calls',       'label' => 'Llamadas'],
            ['key' => 'emails',      'label' => 'Correos'],
            ['key' => 'visits',      'label' => 'Visitas'],
            ['key' => 'agreements',  'label' => 'Acuerdos'],
        ];

        $q = DB::table('management_logs as ml')
            ->join('clients as c', 'c.id', '=', 'ml.client_id')
            ->leftJoin('advisors as a', 'a.id', '=', 'ml.advisor_id')
            ->whereNull('ml.deleted_at');

        if ($this->uen) {
            $q->where('c.uen', $this->uen);
        }
        if ($this->periodFrom) {
            $q->where('ml.contact_date', '>=', $this->periodFrom . '-01');
        }
        if ($this->periodTo) {
            $q->where('ml.contact_date', '<=', $this->periodTo . '-28');
        }

        $rows = $q->select([
                DB::raw('COALESCE(a.name, "Sin asignar") as advisor'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(ml.type = "call") as calls'),
                DB::raw('SUM(ml.type = "email") as emails'),
                DB::raw('SUM(ml.type = "visit") as visits'),
                DB::raw('SUM(ml.type = "agreement") as agreements'),
            ])
            ->groupBy('ml.advisor_id', 'a.name')
            ->orderByDesc('total')
            ->get()->toArray();

        return [$columns, $rows, ['total_rows' => count($rows), 'total_amount' => 0]];
    }

    private function reportActaCompromisos(): array
    {
        $columns = [
            ['key' => 'advisor',        'label' => 'Asesor'],
            ['key' => 'client',         'label' => 'Cliente'],
            ['key' => 'document',       'label' => 'Documento'],
            ['key' => 'agreement',      'label' => 'Acuerdo'],
            ['key' => 'commitment_date', 'label' => 'Fecha compromiso'],
            ['key' => 'contact_datetime', 'label' => 'Fecha / hora gestión'],
        ];

        $rows = $this->actaCompromisosQuery()
            ->orderBy('a.name')
            ->orderBy('c.name')
            ->get()
            ->map(function ($row) {
                $commitment = $row->promised_date
                    ? \Carbon\Carbon::parse($row->promised_date)->format('d/m/Y')
                    : ($row->follow_up_date
                        ? \Carbon\Carbon::parse($row->follow_up_date)->format('d/m/Y')
                        : '—');

                $time = $row->contact_time ? substr((string) $row->contact_time, 0, 5) : null;
                $contact = $row->contact_date
                    ? \Carbon\Carbon::parse($row->contact_date)->format('d/m/Y') . ($time ? " {$time}" : '')
                    : '—';

                return (object) [
                    'advisor'          => $row->advisor ?? 'Sin asignar',
                    'client'           => $row->client,
                    'document'         => $row->document_number ?? '—',
                    'agreement'        => trim($row->type_label . ': ' . $row->subject),
                    'commitment_date'  => $commitment,
                    'contact_datetime' => $contact,
                ];
            })
            ->toArray();

        return [$columns, $rows, ['total_rows' => count($rows), 'total_amount' => 0]];
    }

    private function actaCompromisosQuery(): \Illuminate\Database\Query\Builder
    {
        $q = DB::table('management_logs as ml')
            ->join('clients as c', 'c.id', '=', 'ml.client_id')
            ->leftJoin('advisors as a', 'a.id', '=', 'ml.advisor_id')
            ->leftJoin('portfolio_documents as pd', 'pd.id', '=', 'ml.portfolio_document_id')
            ->whereNull('ml.deleted_at');

        if ($this->uen) {
            $q->where(function ($inner) {
                $inner->where('ml.uen', $this->uen)
                    ->orWhere('c.uen', $this->uen);
            });
        }

        if ($this->channel) {
            $q->where(function ($inner) {
                $inner->where('ml.channel', $this->channel)
                    ->orWhere('c.channel', $this->channel);
            });
        }

        $sessionDate = $this->sessionDate ?: ($this->periodFrom ? $this->periodFrom . '-01' : null);
        if ($sessionDate) {
            $q->whereDate('ml.contact_date', $sessionDate);
        } elseif ($this->periodFrom) {
            $q->where('ml.contact_date', '>=', $this->periodFrom . '-01');
        }
        if ($this->periodTo && ! $this->sessionDate) {
            $q->where('ml.contact_date', '<=', $this->periodTo . '-28');
        }

        if ($this->timeFrom) {
            $q->where('ml.contact_time', '>=', $this->normalizeTimeFilter($this->timeFrom));
        }
        if ($this->timeTo) {
            $q->where('ml.contact_time', '<=', $this->normalizeTimeFilter($this->timeTo, true));
        }

        return $q->select([
            DB::raw('COALESCE(a.name, "Sin asignar") as advisor'),
            'c.name as client',
            'pd.document_number',
            'ml.subject',
            'ml.type',
            DB::raw('CASE ml.type
                WHEN "call" THEN "Llamada"
                WHEN "email" THEN "Correo"
                WHEN "visit" THEN "Visita"
                WHEN "agreement" THEN "Acuerdo"
                WHEN "legal" THEN "Jurídico"
                ELSE "Otro" END as type_label'),
            'ml.promised_date',
            'ml.follow_up_date',
            'ml.contact_date',
            'ml.contact_time',
        ]);
    }

    private function normalizeTimeFilter(string $time, bool $end = false): string
    {
        if (strlen($time) === 5) {
            return $end ? $time . ':59' : $time . ':00';
        }

        return $time;
    }

    private function reportAnalisisVencimiento(): array
    {
        $columns = [
            ['key' => 'bucket',         'label' => 'Rango de mora'],
            ['key' => 'documentos',     'label' => 'Documentos'],
            ['key' => 'clientes',       'label' => 'Clientes'],
            ['key' => 'saldo_total',    'label' => 'Saldo total'],
            ['key' => 'pct',            'label' => '% del total'],
        ];

        $grandTotal = (float) $this->basePortfolioQuery()
            ->sum('pd.pending_amount');

        $buckets = [
            ['label' => 'Al día (0)',         'min' => 0,   'max' => 0],
            ['label' => '1–30 días',           'min' => 1,   'max' => 30],
            ['label' => '31–60 días',          'min' => 31,  'max' => 60],
            ['label' => '61–90 días',          'min' => 61,  'max' => 90],
            ['label' => '91–180 días',         'min' => 91,  'max' => 180],
            ['label' => 'Más de 180 días',     'min' => 181, 'max' => 9999],
        ];

        $rows = [];
        foreach ($buckets as $b) {
            $q = $this->basePortfolioQuery()
                ->where('pd.days_overdue', '>=', $b['min'])
                ->where('pd.days_overdue', '<=', $b['max']);

            $saldo = (float) $q->sum('pd.pending_amount');
            $docs  = $q->count();
            $cls   = $q->distinct()->count('pd.client_id');

            $rows[] = (object) [
                'bucket'      => $b['label'],
                'documentos'  => $docs,
                'clientes'    => $cls,
                'saldo_total' => $saldo,
                'pct'         => $grandTotal > 0 ? round($saldo / $grandTotal * 100, 1) . '%' : '0%',
            ];
        }

        return [$columns, $rows, ['total_rows' => array_sum(array_column($rows, 'documentos')), 'total_amount' => $grandTotal]];
    }
}
