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
            $config = $request->validate([
                'model' => 'required|string',
                'relationships' => 'array',
                'row_dimensions' => 'array',
                'column_dimensions' => 'array',
                'metrics' => 'array',
                'filters' => 'array',
            ]);

            // Validate model exists
            if (!$this->dataSourceManager->isValidModel($config['model'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid model specified',
                ], 422);
            }

            // Build and execute the query
            $result = $this->executePreviewQuery($config);

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
     * Execute preview query with relationships and filters
     */
    protected function executePreviewQuery(array $config): array
    {
        $modelClass = $config['model'];
        $relationships = $config['relationships'] ?? [];
        $rowDimensions = $config['row_dimensions'] ?? [];
        $columnDimensions = $config['column_dimensions'] ?? [];
        $metrics = $config['metrics'] ?? [];
        $filters = $config['filters'] ?? [];

        // Create query builder instance
        $query = $modelClass::query();

        // Join relationships
        foreach ($relationships as $relationName) {
            $query->with($relationName);
        }

        // Build select columns
        $selectColumns = [];
        $groupByColumns = [];

        // Add dimensions to select and group by
        $allDimensions = array_merge($rowDimensions, $columnDimensions);
        foreach ($allDimensions as $dimension) {
            if (str_contains($dimension, '.')) {
                // Related table column - will be handled after eager loading
                continue;
            }
            $selectColumns[] = $dimension;
            $groupByColumns[] = $dimension;
        }

        // Add metrics with aggregation
        foreach ($metrics as $metric) {
            $column = $metric['column'];
            $aggregate = $metric['aggregate'] ?? 'sum';
            $alias = $metric['alias'] ?? "{$column}_{$aggregate}";

            // Handle related columns
            if (str_contains($column, '.')) {
                // For related columns, we need to use subqueries or raw SQL
                // For now, skip aggregation on related columns in preview
                continue;
            }

            $selectColumns[] = \DB::raw("{$aggregate}({$column}) as {$alias}");
        }

        // Apply filters
        foreach ($filters as $filter) {
            $column = $filter['column'];
            $operator = $filter['operator'] ?? '=';
            $value = $filter['value'] ?? null;

            // Handle related columns
            if (str_contains($column, '.')) {
                [$relation, $relColumn] = explode('.', $column, 2);
                $query->whereHas($relation, function ($q) use ($relColumn, $operator, $value) {
                    $this->applyFilterCondition($q, $relColumn, $operator, $value);
                });
                continue;
            }

            $this->applyFilterCondition($query, $column, $operator, $value);
        }

        // Apply select
        if (!empty($selectColumns)) {
            $query->select($selectColumns);
        }

        // Apply group by
        if (!empty($groupByColumns)) {
            $query->groupBy($groupByColumns);
        }

        // Limit results for preview
        $query->limit(100);

        return $query->get()->toArray();
    }

    /**
     * Apply filter condition to query
     */
    protected function applyFilterCondition($query, string $column, string $operator, $value): void
    {
        switch ($operator) {
            case '=':
                $query->where($column, '=', $value);
                break;
            case '!=':
                $query->where($column, '!=', $value);
                break;
            case '>':
                $query->where($column, '>', $value);
                break;
            case '>=':
                $query->where($column, '>=', $value);
                break;
            case '<':
                $query->where($column, '<', $value);
                break;
            case '<=':
                $query->where($column, '<=', $value);
                break;
            case 'like':
                $query->where($column, 'like', "%{$value}%");
                break;
            case 'in':
                $values = is_array($value) ? $value : explode(',', $value);
                $query->whereIn($column, array_map('trim', $values));
                break;
            case 'not_in':
                $values = is_array($value) ? $value : explode(',', $value);
                $query->whereNotIn($column, array_map('trim', $values));
                break;
            case 'is_null':
                $query->whereNull($column);
                break;
            case 'is_not_null':
                $query->whereNotNull($column);
                break;
            default:
                $query->where($column, '=', $value);
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
            // Check if auth is enabled and user is authenticated
            $authEnabled = config('visual-report-builder.auth.enabled', false);
            $userId = auth()->id();

            // If auth is enabled, user must be authenticated
            if ($authEnabled && !$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - Please login to save templates',
                ], 401);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'model' => 'required|string',
                'icon' => 'nullable|string|max:10',
                'category' => 'required|string|max:100',
                'relationships' => 'array',
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

            // Build template configuration
            $templateConfig = [
                'relationships' => $validated['relationships'] ?? [],
                'row_dimensions' => $validated['row_dimensions'] ?? [],
                'column_dimensions' => $validated['column_dimensions'] ?? [],
            ];

            // Create report template - set created_by to null if auth is disabled
            $template = ReportTemplate::create([
                'created_by' => $userId, // Will be null if auth is disabled
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'model' => $validated['model'],
                'icon' => $validated['icon'] ?? '📊',
                'category' => $validated['category'],
                'is_public' => true, // Per user requirement
                'is_active' => true,
                'dimensions' => $templateConfig, // Store full config including relationships
                'metrics' => $validated['metrics'],
                'filters' => $validated['filters'] ?? [], // Store filter definitions
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
