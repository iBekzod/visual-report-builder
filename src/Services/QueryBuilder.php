<?php

namespace Ibekzod\VisualReportBuilder\Services;

use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

class QueryBuilder
{
    protected FilterManager $filterManager;

    /**
     * Database driver name
     */
    protected ?string $driver = null;

    public function __construct()
    {
        $this->filterManager = new FilterManager();
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
        $q = $this->getQuoteChar($query);

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
                $selectColumns[] = $this->buildAggregateSelect($column, $aggregate, $alias, $q);
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
            $quotedDims = array_map(fn($dim) => $this->quoteIdentifier($dim, $q), $allDims);
            $query->selectRaw(implode(', ', $quotedDims));

            // Add metric aggregates
            foreach ($config['metrics'] ?? [] as $metric) {
                $column = $metric['column'] ?? null;
                $aggregate = $metric['aggregate'] ?? 'sum';
                $alias = $metric['alias'] ?? "{$column}_{$aggregate}";

                if ($column) {
                    $query->selectRaw($this->buildAggregateSelect($column, $aggregate, $alias, $q));
                }
            }
        } else {
            // No dimensions, just select metrics
            foreach ($config['metrics'] ?? [] as $metric) {
                $column = $metric['column'] ?? null;
                $aggregate = $metric['aggregate'] ?? 'sum';
                $alias = $metric['alias'] ?? "{$column}_{$aggregate}";

                if ($column) {
                    $query->selectRaw($this->buildAggregateSelect($column, $aggregate, $alias, $q));
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
            $allowedOperators = ['=', '!=', '<>', '>', '<', '>=', '<=', 'like', 'not like'];
            foreach ($config['having'] as $having) {
                $column = $having['column'] ?? null;
                $operator = strtolower($having['operator'] ?? '=');
                $value = $having['value'] ?? null;

                if ($column && in_array($operator, $allowedOperators)) {
                    $quotedColumn = $this->quoteIdentifier($column, $q);
                    $query->havingRaw("{$quotedColumn} {$operator} ?", [$value]);
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
