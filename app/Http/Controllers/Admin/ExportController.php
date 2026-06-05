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
        $clientId = $request->filled('client_id') ? (int) $request->query('client_id') : null;

        return $this->export->exportAgingReport($period, $templateId, $clientId);
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

    public function commitmentActa(Request $request)
    {
        $sessionDate = $request->query('session_date');
        abort_if(! $sessionDate, 422, 'La fecha de sesión es requerida.');

        return $this->export->exportCommitmentActa(
            $sessionDate,
            $request->query('uen'),
            $request->query('channel'),
            $request->query('time_from'),
            $request->query('time_to'),
        );
    }
}
