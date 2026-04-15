<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ExportService;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function __construct(private ExportService $export) {}

    public function portfolioAnalysis(Request $request)
    {
        $period = $request->query('period');
        $templateId = $request->filled('template_id') ? (int) $request->query('template_id') : null;

        if (! $period) {
            $period = \Illuminate\Support\Facades\DB::table('portfolio_documents')
                ->whereNotNull('period_date')
                ->max('period_date');
        }

        if (! $period) {
            abort(404, 'No hay datos de cartera disponibles.');
        }

        $period = substr($period, 0, 7); // YYYY-MM

        return $this->export->exportAgingReport($period, $templateId);
    }

    public function collectionDetails(Request $request)
    {
        $period = $request->query('period');
        abort_if(! $period, 422, 'Período requerido.');
        return $this->export->exportCollectionDetails($period);
    }

    public function reconciliation(Request $request)
    {
        $period = $request->query('period');
        abort_if(! $period, 422, 'Período requerido.');
        return $this->export->exportReconciliation($period);
    }
}
