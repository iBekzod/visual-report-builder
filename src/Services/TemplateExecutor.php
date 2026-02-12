<?php

namespace Ibekzod\VisualReportBuilder\Services;

use Ibekzod\VisualReportBuilder\Models\ReportTemplate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class TemplateExecutor
{
    protected FilterManager $filterManager;
    protected AggregateCalculator $aggregateCalculator;

    /**
     * Database driver name
     */
    protected ?string $driver = null;

    public function __construct(
        FilterManager $filterManager,
        AggregateCalculator $aggregateCalculator
    ) {
        $this->filterManager = $filterManager;
        $this->aggregateCalculator = $aggregateCalculator;
    }

    /**
     * Get the identifier quote character for the current database driver
     *
     * @param Builder $query
     * @return string
     */
    protected function getQuoteChar(Builder $query): string
    {
        if ($this->driver === null) {
            $this->driver = $query->getConnection()->getDriverName();
        }

        return match ($this->driver) {
            'mysql', 'mariadb' => '`',
            'pgsql' => '"',
            'sqlite' => '"',
            'sqlsrv' => '"',
            default => '"',
        };
    }

    /**
     * Quote an identifier for SQL
     *
     * @param string $identifier
     * @param string $quoteChar
     * @return string
     */
    protected function quoteIdentifier(string $identifier, string $quoteChar): string
    {
        return $quoteChar . $identifier . $quoteChar;
    }

    /**
     * Execute a template with given filters (supports pagination)
     *
     * @param ReportTemplate $template
     * @param array $appliedFilters
     * @param int|null $page Page number (null for all data)
     * @param int $perPage Items per page
     * @return array
     */
    public function execute(ReportTemplate $template, array $appliedFilters = [], ?int $page = null, int $perPage = 20): array
    {
        $startTime = microtime(true);

        // Get model
        $model = $template->model;
        if (!class_exists($model)) {
            throw new InvalidArgumentException("Model {$model} does not exist");
        }

        // Build query
        $query = $model::query();

        // Apply template filters
        $query = $this->applyFilters($query, $template, $appliedFilters);

        // Get dimensions and metrics from template
        $dimensions = $template->getDimensions();
        $metrics = $template->getMetrics();

        // Build result with optional pagination
        $result = $this->queryDataWithPagination($query, $dimensions, $metrics, $page, $perPage);

        // Calculate execution time
        $executionTime = round((microtime(true) - $startTime) * 1000);

        $response = [
            'success' => true,
            'data' => $result['data'],
            'dimensions' => $dimensions,
            'metrics' => $metrics,
            'execution_time_ms' => $executionTime,
            'record_count' => $result['total'],
        ];

        // Add pagination info if pagination was requested
        if ($page !== null) {
            $response['pagination'] = [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $result['total'],
                'total_pages' => (int) ceil($result['total'] / $perPage),
            ];
        }

        return $response;
    }

    /**
     * Apply filters to query based on template definition and applied values
     *
     * @param Builder $query
     * @param ReportTemplate $template
     * @param array $appliedFilters
     * @return Builder
     */
    protected function applyFilters(Builder $query, ReportTemplate $template, array $appliedFilters): Builder
    {
        $templateFilters = $template->getFilters();

        foreach ($templateFilters as $filterDef) {
            $column = $filterDef['column'];
            $operator = $filterDef['operator'] ?? '=';
            $isRequired = $filterDef['is_required'] ?? false;

            // Get applied value for this filter
            $value = $appliedFilters[$column] ?? $filterDef['default_value'] ?? null;

            // Skip if not required and no value provided
            if (!$isRequired && $value === null) {
                continue;
            }

            // Apply filter to query
            $query = $this->applyFilterCondition($query, $column, $operator, $value);
        }

        return $query;
    }

    /**
     * Apply single filter condition to query
     *
     * @param Builder $query
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return Builder
     */
    protected function applyFilterCondition(Builder $query, string $column, string $operator, mixed $value): Builder
    {
        return match ($operator) {
            '=' => $query->where($column, '=', $value),
            '!=' => $query->where($column, '!=', $value),
            '>' => $query->where($column, '>', $value),
            '<' => $query->where($column, '<', $value),
            '>=' => $query->where($column, '>=', $value),
            '<=' => $query->where($column, '<=', $value),
            'in' => $query->whereIn($column, (array)$value),
            'not_in' => $query->whereNotIn($column, (array)$value),
            'like' => $query->where($column, 'like', "%{$value}%"),
            'not_like' => $query->where($column, 'not like', "%{$value}%"),
            'between' => is_array($value) ? $query->whereBetween($column, $value) : $query,
            'is_null' => $query->whereNull($column),
            'is_not_null' => $query->whereNotNull($column),
            default => $query,
        };
    }

    /**
     * Query data from database with dimensions and metrics (with pagination support)
     *
     * @param Builder $query
     * @param array $dimensions
     * @param array $metrics
     * @param int|null $page Page number (null for all data)
     * @param int $perPage Items per page
     * @return array
     */
    protected function queryDataWithPagination(Builder $query, array $dimensions, array $metrics, ?int $page = null, int $perPage = 20): array
    {
        $q = $this->getQuoteChar($query);

        // Select dimensions
        $selectColumns = [];
        foreach ($dimensions as $dim) {
            $selectColumns[] = $this->quoteIdentifier($dim['column'], $q);
        }

        // Add metrics as aggregates
        foreach ($metrics as $metric) {
            $column = $metric['column'];
            $aggregate = $metric['aggregate'] ?? 'sum';
            $alias = $metric['alias'] ?? "{$column}_{$aggregate}";

            $selectColumns[] = $this->buildAggregateSelect($column, $aggregate, $alias, $q);
        }

        // Build select statement
        if (!empty($selectColumns)) {
            $query->selectRaw(implode(', ', $selectColumns));
        }

        // Group by dimensions
        if (!empty($dimensions)) {
            $groupByColumns = array_map(fn($d) => $d['column'], $dimensions);
            $query->groupBy($groupByColumns);
        }

        // If no pagination, return all data
        if ($page === null) {
            $data = $query->get()->toArray();
            return [
                'data' => $data,
                'total' => count($data),
            ];
        }

        // Clone query for counting (before adding limit/offset)
        $countQuery = clone $query;

        // Get total count
        // For grouped queries, we need to count the number of groups
        $total = \DB::table(\DB::raw("({$countQuery->toSql()}) as sub"))
            ->mergeBindings($countQuery->getQuery())
            ->count();

        // Apply pagination
        $offset = ($page - 1) * $perPage;
        $data = $query->skip($offset)->take($perPage)->get()->toArray();

        return [
            'data' => $data,
            'total' => $total,
        ];
    }

    /**
     * Query data from database with dimensions and metrics (legacy method for backward compatibility)
     *
     * @param Builder $query
     * @param array $dimensions
     * @param array $metrics
     * @return Collection|array
     */
    protected function queryData(Builder $query, array $dimensions, array $metrics): Collection|array
    {
        $result = $this->queryDataWithPagination($query, $dimensions, $metrics, null, 0);
        return $result['data'];
    }

    /**
     * Build aggregate select statement
     *
     * @param string $column
     * @param string $aggregate
     * @param string $alias
     * @param string $q Quote character for identifiers
     * @return string
     */
    protected function buildAggregateSelect(string $column, string $aggregate, string $alias, string $q = '"'): string
    {
        $quotedColumn = $this->quoteIdentifier($column, $q);
        $quotedAlias = $this->quoteIdentifier($alias, $q);

        return match ($aggregate) {
            'sum' => "SUM({$quotedColumn}) as {$quotedAlias}",
            'avg' => "AVG({$quotedColumn}) as {$quotedAlias}",
            'min' => "MIN({$quotedColumn}) as {$quotedAlias}",
            'max' => "MAX({$quotedColumn}) as {$quotedAlias}",
            'count' => "COUNT({$quotedColumn}) as {$quotedAlias}",
            'count_distinct' => "COUNT(DISTINCT {$quotedColumn}) as {$quotedAlias}",
            'value' => "{$quotedColumn} as {$quotedAlias}",
            default => "{$quotedColumn} as {$quotedAlias}",
        };
    }
}
