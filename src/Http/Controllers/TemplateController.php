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
        $this->middleware('auth');
    }

    /**
     * Get all templates available to user's role
     */
    public function index()
    {
        $user = auth()->user();
        $templates = ReportTemplate::active()
            ->where(function ($q) use ($user) {
                $q->where('is_public', true)
                    ->orWhere('created_by', $user->id);
            })
            ->with(['results' => function($q) use ($user) {
                $q->where('user_id', $user->id);
            }])
            ->get()
            ->map(function ($template) use ($user) {
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
                        ->byUser($user->id)
                        ->latest()
                        ->limit(3)
                        ->get(['id', 'name', 'view_type', 'created_at'])
                ];
            });

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
        // Authorization: User can view if template is public OR user created it
        if (!$template->is_public && $template->created_by !== auth()->id()) {
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
        // Authorization: User can execute if template is public OR user created it
        if (!$template->is_public && $template->created_by !== auth()->id()) {
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
     * Export report
     */
    public function export(Request $request, ReportResult $result)
    {
        if ($result->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $format = $request->input('format', 'csv');
        $exporter = app('visual-report-builder')->getExporterFactory()->create($format);

        return $exporter->exportAsFile($result->data, "{$result->name}.{$exporter->getExtension()}");
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
            $values = array_column($data, $column);

            $summary[$column] = [
                'sum' => array_sum($values),
                'avg' => count($values) > 0 ? array_sum($values) / count($values) : 0,
                'min' => min($values) ?: 0,
                'max' => max($values) ?: 0,
                'count' => count($values),
            ];
        }

        return $summary;
    }
}
