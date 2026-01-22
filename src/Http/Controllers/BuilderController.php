<?php

namespace Ibekzod\VisualReportBuilder\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Ibekzod\VisualReportBuilder\Models\ReportTemplate;
use Ibekzod\VisualReportBuilder\Models\TemplateFilter;
use Ibekzod\VisualReportBuilder\Services\DataSourceManager;

class BuilderController extends Controller
{
    protected DataSourceManager $dataSourceManager;

    public function __construct(DataSourceManager $dataSourceManager)
    {
        // Middleware is now configured at route level via config('visual-report-builder.auth.api_middleware')
        $this->dataSourceManager = $dataSourceManager;
    }

    /**
     * Show the builder UI
     */
    public function index()
    {
        return view('visual-report-builder::builder');
    }

    /**
     * Get available models
     */
    public function models()
    {
        $builder = app('visual-report-builder');
        $models = $builder->getAvailableModels();

        return response()->json($models);
    }

    /**
     * Get model metadata
     */
    public function modelMetadata(string $model)
    {
        try {
            $builder = app('visual-report-builder');
            $metadata = $builder->getMetadata($model);

            return response()->json($metadata);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Invalid model',
            ], 400);
        }
    }

    /**
     * Get dimensions for model
     */
    public function dimensions(string $model)
    {
        try {
            $builder = app('visual-report-builder');
            $dimensions = $builder->getDimensions($model);

            return response()->json($dimensions);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid model'], 400);
        }
    }

    /**
     * Get metrics for model
     */
    public function metrics(string $model)
    {
        try {
            $builder = app('visual-report-builder');
            $metrics = $builder->getMetrics($model);

            return response()->json($metrics);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid model'], 400);
        }
    }

    /**
     * Preview report configuration
     */
    public function preview(Request $request)
    {
        try {
            $builder = app('visual-report-builder');
            $config = $request->validate([
                'model' => 'required|string',
                'row_dimensions' => 'array',
                'column_dimensions' => 'array',
                'metrics' => 'array',
                'filters' => 'array',
            ]);

            $result = $builder->execute($config);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get model relationships (for JOIN support)
     */
    public function relationships(string $model)
    {
        try {
            $relationships = $this->dataSourceManager->getModelRelationships($model);

            return response()->json([
                'success' => true,
                'relationships' => $relationships,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Save template from builder
     */
    public function saveTemplate(Request $request)
    {
        try {
            // Check if auth is enabled
            if (!config('visual-report-builder.auth.enabled', true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'model' => 'required|string',
                'icon' => 'nullable|string|max:10',
                'category' => 'required|string|max:100',
                'row_dimensions' => 'array',
                'column_dimensions' => 'array',
                'metrics' => 'required|array|min:1',
                'filters' => 'array',
                'default_view' => 'array',
            ]);

            // Validate model exists
            if (!$this->dataSourceManager->isValidModel($validated['model'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid model specified',
                ], 422);
            }

            // Create report template
            $template = ReportTemplate::create([
                'created_by' => auth()->id(),
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'model' => $validated['model'],
                'icon' => $validated['icon'] ?? '📊',
                'category' => $validated['category'],
                'is_public' => true, // Per user requirement
                'is_active' => true,
                'dimensions' => array_merge(
                    $validated['row_dimensions'] ?? [],
                    $validated['column_dimensions'] ?? []
                ),
                'metrics' => $validated['metrics'],
                'default_view' => $validated['default_view'] ?? ['type' => 'table'],
                'chart_config' => [],
            ]);

            // Create filters if provided
            foreach ($validated['filters'] ?? [] as $filter) {
                TemplateFilter::create([
                    'report_template_id' => $template->id,
                    'column' => $filter['column'] ?? null,
                    'label' => $filter['label'] ?? null,
                    'type' => $filter['type'] ?? 'text',
                    'operator' => $filter['operator'] ?? '=',
                    'options' => $filter['options'] ?? null,
                    'is_required' => $filter['is_required'] ?? false,
                    'is_active' => true,
                    'sort_order' => $filter['sort_order'] ?? 0,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Template created successfully',
                'template_id' => $template->id,
                'template' => $template,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
