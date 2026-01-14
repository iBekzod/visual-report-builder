<?php

namespace Ibekzod\VisualReportBuilder\Services;

use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

class QueryBuilder
{
    protected FilterManager $filterManager;

    public function __construct()
    {
        $this->filterManager = new FilterManager();
    }

    /**
     * Build Eloquent query from report configuration
     *
     * @param array $config
     * @param string $model
     * @return Builder
     */
    public function build(array $config, string $model): Builder
    {
        if (!class_exists($model)) {
            throw new InvalidArgumentException("Model {$model} does not exist");
        }

        $query = $model::query();

        // Add dimensions to select
        $dimensions = $config['row_dimensions'] ?? [];
        $columnDims = $config['column_dimensions'] ?? [];
        $allDims = array_merge($dimensions, $columnDims);

        // Build select statement
        $selectColumns = [];

        // Add dimensions
        if (!empty($allDims)) {
            $selectColumns = array_merge($selectColumns, $allDims);
        }

        // Add metric aggregates
        foreach ($config['metrics'] ?? [] as $metric) {
            $column = $metric['column'] ?? null;
            $aggregate = $metric['aggregate'] ?? 'sum';
            $alias = $metric['alias'] ?? "{$column}_{$aggregate}";

            if ($column) {
                $selectColumns[] = $this->buildAggregateSelect($column, $aggregate, $alias);
            }
        }

        // If we have dimensions, add them first
        if (!empty($allDims)) {
            // Remove duplicates from selectColumns
            $selectColumns = array_unique($selectColumns);
            $query->selectRaw(implode(', ', array_filter(
                array_map(function ($col) use ($allDims) {
                    return in_array($col, $allDims) ? null : $col;
                }, $selectColumns),
                function ($col) {
                    return $col !== null;
                }
            )));

            // Select dimensions
            $query->selectRaw('`' . implode('`, `', $allDims) . '`');

            // Add metric aggregates
            foreach ($config['metrics'] ?? [] as $metric) {
                $column = $metric['column'] ?? null;
                $aggregate = $metric['aggregate'] ?? 'sum';
                $alias = $metric['alias'] ?? "{$column}_{$aggregate}";

                if ($column) {
                    $query->selectRaw($this->buildAggregateSelect($column, $aggregate, $alias));
                }
            }
        } else {
            // No dimensions, just select metrics
            foreach ($config['metrics'] ?? [] as $metric) {
                $column = $metric['column'] ?? null;
                $aggregate = $metric['aggregate'] ?? 'sum';
                $alias = $metric['alias'] ?? "{$column}_{$aggregate}";

                if ($column) {
                    $query->selectRaw($this->buildAggregateSelect($column, $aggregate, $alias));
                }
            }
        }

        // Apply filters
        if (!empty($config['filters'])) {
            $query = $this->filterManager->applyToQuery($query, $config['filters']);
        }

        // Apply GROUP BY if we have dimensions
        if (!empty($allDims)) {
            $query->groupBy($allDims);
        }

        // Apply HAVING clause if provided
        if (!empty($config['having'])) {
            foreach ($config['having'] as $having) {
                $column = $having['column'] ?? null;
                $operator = $having['operator'] ?? '=';
                $value = $having['value'] ?? null;

                if ($column) {
                    $query->havingRaw("{$column} {$operator} ?", [$value]);
                }
            }
        }

        // Apply sorting
        if (!empty($config['order_by'])) {
            foreach ($config['order_by'] as $orderBy) {
                $column = $orderBy['column'] ?? null;
                $direction = $orderBy['direction'] ?? 'asc';

                if ($column) {
                    $query->orderBy($column, $direction);
                }
            }
        } else if (!empty($allDims)) {
            // Default sort by dimensions
            $query->orderBy($allDims[0], 'asc');
        }

        // Apply limit
        if (!empty($config['limit'])) {
            $query->limit($config['limit']);
        }

        // Apply offset
        if (!empty($config['offset'])) {
            $query->offset($config['offset']);
        }

        return $query;
    }

    /**
     * Build aggregate select statement
     *
     * @param string $column
     * @param string $aggregate
     * @param string $alias
     * @return string
     */
    protected function buildAggregateSelect(string $column, string $aggregate, string $alias): string
    {
        return match ($aggregate) {
            'sum' => "SUM(`{$column}`) as `{$alias}`",
            'avg' => "AVG(`{$column}`) as `{$alias}`",
            'min' => "MIN(`{$column}`) as `{$alias}`",
            'max' => "MAX(`{$column}`) as `{$alias}`",
            'count' => "COUNT(`{$column}`) as `{$alias}`",
            'count_distinct' => "COUNT(DISTINCT `{$column}`) as `{$alias}`",
            'value' => "`{$column}` as `{$alias}`",
            default => "{$column} as {$alias}",
        };
    }

    /**
     * Get raw query for debugging
     *
     * @param Builder $query
     * @return string
     */
    public function toRawSql(Builder $query): string
    {
        return $query->toSql();
    }
}
