<?php

namespace Ibekzod\VisualReportBuilder\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Ibekzod\VisualReportBuilder\Models\ReportTemplate;
use Ibekzod\VisualReportBuilder\Models\ReportResult;
use Ibekzod\VisualReportBuilder\Services\TemplateExecutor;

class TemplateController extends Controller
{
    protected TemplateExecutor $executor;

    public function __construct(TemplateExecutor $executor)
    {
        $this->executor = $executor;
        // Middleware is now configured at route level via config('visual-report-builder.auth.web_middleware')
    }

    /**
     * Get all templates available to user's role
     */
    public function index()
    {
        // Check if auth is enabled
        $authEnabled = config('visual-report-builder.auth.enabled', false);
        $userId = $authEnabled ? auth()->id() : null;

        if (!$authEnabled) {
            // Return public templates only when auth is disabled
            $templates = ReportTemplate::active()
                ->where('is_public', true)
                ->get()
                ->map(function ($template) {
                    return [
                        'id' => $template->id,
                        'name' => $template->name,
                        'description' => $template->description,
                        'icon' => $template->icon,
                        'category' => $template->category,
                        'model' => $template->model,
                        'dimensions' => $template->getDimensions(),
                        'metrics' => $template->getMetrics(),
                        'filters' => $template->getFilters(),
                        'default_view' => $template->default_view,
                        'recent_results' => [],
                    ];
                });
        } else {
            $templates = ReportTemplate::active()
                ->where(function ($q) use ($userId) {
                    $q->where('is_public', true)
                        ->orWhere('created_by', $userId);
                })
                ->with(['results' => function($q) use ($userId) {
                    $q->where('user_id', $userId);
                }])
                ->get()
                ->map(function ($template) use ($userId) {
                    return [
                        'id' => $template->id,
                        'name' => $template->name,
                        'description' => $template->description,
                        'icon' => $template->icon,
                        'category' => $template->category,
                        'model' => $template->model,
                        'dimensions' => $template->getDimensions(),
                        'metrics' => $template->getMetrics(),
                        'filters' => $template->getFilters(),
                        'default_view' => $template->default_view,
                        'recent_results' => $template->results()
                            ->byUser($userId)
                            ->latest()
                            ->limit(3)
                            ->get(['id', 'name', 'view_type', 'created_at'])
                    ];
                });
        }

        return response()->json([
            'templates' => $templates,
            'categories' => ReportTemplate::getCategories(),
        ]);
    }

    /**
     * Get single template with metadata
     */
    public function show(ReportTemplate $template)
    {
        // Check if auth is enabled
        $authEnabled = config('visual-report-builder.auth.enabled', false);

        // Authorization: User can view if template is public OR (auth is enabled AND user created it)
        if (!$template->is_public && ($authEnabled && $template->created_by !== auth()->id())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'id' => $template->id,
            'name' => $template->name,
            'description' => $template->description,
            'icon' => $template->icon,
            'category' => $template->category,
            'model' => $template->model,
            'dimensions' => $template->getDimensions(),
            'metrics' => $template->getMetrics(),
            'filters' => $template->getFilters(),
            'default_view' => $template->default_view,
            'chart_config' => $template->chart_config,
            'view_types' => $template->getViewTypes(),
        ]);
    }

    /**
     * Execute template with filters
     */
    public function execute(Request $request, ReportTemplate $template)
    {
        // Check if auth is enabled
        $authEnabled = config('visual-report-builder.auth.enabled', false);

        // Authorization: User can execute if template is public OR (auth is enabled AND user created it)
        if (!$template->is_public && ($authEnabled && $template->created_by !== auth()->id())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $appliedFilters = $request->input('filters', []);
            $viewType = $request->input('view_type', $template->default_view['type'] ?? 'table');
            $chartConfig = $request->input('chart_config', $template->chart_config ?? []);

            // Execute template
            $result = $this->executor->execute($template, $appliedFilters);

            // Format data for frontend
            $formattedData = $this->formatDataForView($result['data'], $viewType, $template);

            return response()->json([
                'success' => true,
                'data' => $formattedData,
                'metadata' => [
                    'dimensions' => $result['dimensions'],
                    'metrics' => $result['metrics'],
                    'view_type' => $viewType,
                    'execution_time_ms' => $result['execution_time_ms'],
                    'record_count' => $result['record_count'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Save a report result
     */
    public function saveResult(Request $request, ReportTemplate $template)
    {
        // Check if auth is enabled
        if (!config('visual-report-builder.auth.enabled', false)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'applied_filters' => 'array',
            'view_type' => 'required|string',
            'view_config' => 'array',
            'data' => 'required|array',
        ]);

        $result = ReportResult::create([
            'report_template_id' => $template->id,
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'applied_filters' => $validated['applied_filters'] ?? [],
            'view_type' => $validated['view_type'],
            'view_config' => $validated['view_config'] ?? [],
            'data' => $validated['data'],
            'executed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Report saved successfully',
            'result_id' => $result->id,
        ]);
    }

    /**
     * Get saved reports for user
     */
    public function savedReports(Request $request, ReportTemplate $template)
    {
        // Check if auth is enabled
        if (!config('visual-report-builder.auth.enabled', false)) {
            return response()->json([]);
        }

        $reports = $template->results()
            ->byUser(auth()->id())
            ->latest()
            ->get(['id', 'name', 'description', 'view_type', 'is_favorite', 'created_at', 'executed_at'])
            ->map(fn($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'description' => $r->description,
                'view_type' => $r->view_type,
                'is_favorite' => $r->is_favorite,
                'created_at' => $r->created_at,
                'executed_at' => $r->executed_at,
            ]);

        return response()->json($reports);
    }

    /**
     * Load saved report
     */
    public function loadResult(ReportResult $result)
    {
        // Check if auth is enabled
        if (!config('visual-report-builder.auth.enabled', false)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check authorization
        if ($result->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $result->recordView();

        return response()->json([
            'id' => $result->id,
            'name' => $result->name,
            'description' => $result->description,
            'applied_filters' => $result->applied_filters,
            'view_type' => $result->view_type,
            'view_config' => $result->view_config,
            'data' => $result->data,
            'execution_time_ms' => $result->execution_time_ms,
            'created_at' => $result->created_at,
        ]);
    }

    /**
     * Toggle favorite
     */
    public function toggleFavorite(ReportResult $result)
    {
        // Check if auth is enabled
        if (!config('visual-report-builder.auth.enabled', false)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($result->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($result->is_favorite) {
            $result->removeFromFavorite();
        } else {
            $result->markAsFavorite();
        }

        return response()->json(['is_favorite' => $result->is_favorite]);
    }

    /**
     * Export saved report
     */
    public function export(Request $request, ReportResult $result)
    {
        // Check if auth is enabled
        if (!config('visual-report-builder.auth.enabled', false)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($result->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $format = $request->input('format', 'csv');
        $exporter = app('visual-report-builder')->getExporterFactory()->create($format);

        return $exporter->exportAsFile($result->data, "{$result->name}.{$exporter->getExtension()}");
    }

    /**
     * Export template data directly (without saving first)
     */
    public function exportDirect(Request $request, ReportTemplate $template, string $format)
    {
        // Authorization: User can export if template is public OR (auth is enabled AND user created it)
        $authEnabled = config('visual-report-builder.auth.enabled', false);
        if (!$template->is_public && ($authEnabled && $template->created_by !== auth()->id())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            // Get data from request or execute template
            $data = $request->input('data');

            if (empty($data)) {
                // Execute template to get fresh data
                $appliedFilters = $request->input('filters', []);
                $result = $this->executor->execute($template, $appliedFilters);
                $data = $result['data'];
            }

            // Validate format
            $validFormats = ['csv', 'json', 'excel', 'pdf'];
            if (!in_array($format, $validFormats)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid export format. Supported: ' . implode(', ', $validFormats)
                ], 400);
            }

            // Generate filename
            $filename = str_replace(' ', '_', $template->name) . '_' . date('Y-m-d_His');

            // Handle different export formats
            switch ($format) {
                case 'csv':
                    return $this->exportAsCsv($data, $filename);
                case 'json':
                    return $this->exportAsJson($data, $filename);
                case 'excel':
                    return $this->exportAsExcel($data, $filename);
                case 'pdf':
                    return $this->exportAsPdf($data, $filename, $template);
                default:
                    return $this->exportAsCsv($data, $filename);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Export data as CSV
     */
    protected function exportAsCsv(array $data, string $filename)
    {
        if (empty($data)) {
            return response()->json(['success' => false, 'message' => 'No data to export'], 400);
        }

        $headers = array_keys($data[0]);

        $callback = function() use ($data, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($data as $row) {
                fputcsv($file, array_values($row));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ]);
    }

    /**
     * Export data as JSON
     */
    protected function exportAsJson(array $data, string $filename)
    {
        return response()->json($data)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '.json"');
    }

    /**
     * Export data as Excel (simple CSV with excel-friendly encoding)
     */
    protected function exportAsExcel(array $data, string $filename)
    {
        if (empty($data)) {
            return response()->json(['success' => false, 'message' => 'No data to export'], 400);
        }

        $headers = array_keys($data[0]);

        $callback = function() use ($data, $headers) {
            $file = fopen('php://output', 'w');

            // Add BOM for Excel UTF-8 compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, $headers);

            foreach ($data as $row) {
                fputcsv($file, array_values($row));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.xlsx"',
        ]);
    }

    /**
     * Export data as PDF (simple HTML table)
     */
    protected function exportAsPdf(array $data, string $filename, ReportTemplate $template)
    {
        if (empty($data)) {
            return response()->json(['success' => false, 'message' => 'No data to export'], 400);
        }

        $headers = array_keys($data[0]);

        // Generate HTML
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . $template->name . '</title>';
        $html .= '<style>body{font-family:Arial,sans-serif;font-size:12px;margin:20px;}';
        $html .= 'h1{font-size:18px;margin-bottom:20px;}';
        $html .= 'table{border-collapse:collapse;width:100%;}';
        $html .= 'th,td{border:1px solid #ddd;padding:8px;text-align:left;}';
        $html .= 'th{background-color:#f2f2f2;font-weight:bold;}';
        $html .= 'tr:nth-child(even){background-color:#f9f9f9;}</style></head><body>';
        $html .= '<h1>' . htmlspecialchars($template->name) . '</h1>';
        $html .= '<p>Generated: ' . date('Y-m-d H:i:s') . '</p>';
        $html .= '<table><thead><tr>';

        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars(ucwords(str_replace('_', ' ', $header))) . '</th>';
        }

        $html .= '</tr></thead><tbody>';

        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $value) {
                $html .= '<td>' . htmlspecialchars($value ?? '') . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</tbody></table></body></html>';

        // Return as downloadable HTML (browsers can print to PDF)
        return response($html, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.html"',
        ]);
    }

    /**
     * Format data for specific view type
     */
    protected function formatDataForView(array $data, string $viewType, ReportTemplate $template): array
    {
        // Prepare metadata for charts
        $dimensions = $template->getDimensions();
        $metrics = $template->getMetrics();

        return [
            'rows' => $data,
            'dimensions' => $dimensions,
            'metrics' => $metrics,
            'view_type' => $viewType,
            'summary' => $this->calculateSummary($data, $metrics),
        ];
    }

    /**
     * Delete a saved report
     */
    public function deleteResult(ReportResult $result)
    {
        // Check if auth is enabled
        if (!config('visual-report-builder.auth.enabled', false)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($result->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $result->delete();

        return response()->json(['success' => true, 'message' => 'Report deleted successfully']);
    }

    /**
     * Share report with another user
     */
    public function share(Request $request, ReportResult $result)
    {
        // Check if auth is enabled
        if (!config('visual-report-builder.auth.enabled', false)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($result->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'can_edit' => 'boolean',
            'can_share' => 'boolean',
        ]);

        // TODO: Implement report sharing logic with a shares table
        // This would typically involve creating a ReportShare model and recording who the report is shared with

        return response()->json(['success' => true, 'message' => 'Report shared successfully']);
    }

    /**
     * Stop sharing report with another user
     */
    public function unshare(Request $request, ReportResult $result)
    {
        // Check if auth is enabled
        if (!config('visual-report-builder.auth.enabled', false)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($result->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        // TODO: Implement unshare logic - remove from report_shares table

        return response()->json(['success' => true, 'message' => 'Report unshared successfully']);
    }

    /**
     * Calculate summary statistics
     */
    protected function calculateSummary(array $data, array $metrics): array
    {
        $summary = [];

        foreach ($metrics as $metric) {
            $column = $metric['alias'] ?? $metric['column'];
            $values = array_filter(array_column($data, $column), fn($v) => is_numeric($v));

            $summary[$column] = [
                'sum' => !empty($values) ? array_sum($values) : 0,
                'avg' => count($values) > 0 ? array_sum($values) / count($values) : 0,
                'min' => !empty($values) ? min($values) : 0,
                'max' => !empty($values) ? max($values) : 0,
                'count' => count($values),
            ];
        }

        return $summary;
    }
}
