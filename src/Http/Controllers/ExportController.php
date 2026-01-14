<?php

namespace Ibekzod\VisualReportBuilder\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Ibekzod\VisualReportBuilder\Models\Report;

class ExportController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Export report as CSV
     */
    public function csv(Request $request, Report $report)
    {
        $this->authorize('view', $report);

        try {
            $data = $report->execute();
            $builder = app('visual-report-builder');

            return $builder->exportAsFile(
                $data,
                'csv',
                $report->name . '.csv'
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Export report as Excel
     */
    public function excel(Request $request, Report $report)
    {
        $this->authorize('view', $report);

        try {
            $data = $report->execute();
            $builder = app('visual-report-builder');
            $exporter = $builder->getExporterFactory()->create('excel');

            return $exporter->exportAsFile(
                $data,
                $report->name . '.xlsx'
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Export report as PDF
     */
    public function pdf(Request $request, Report $report)
    {
        $this->authorize('view', $report);

        try {
            $data = $report->execute();
            $builder = app('visual-report-builder');
            $exporter = $builder->getExporterFactory()->create('pdf');

            return $exporter->exportAsFile(
                $data,
                $report->name . '.pdf',
                ['title' => $report->name]
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Export report as JSON
     */
    public function json(Request $request, Report $report)
    {
        $this->authorize('view', $report);

        try {
            $data = $report->execute();

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
