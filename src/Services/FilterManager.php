<?php

namespace Ibekzod\VisualReportBuilder\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FilterManager
{
    /**
     * Apply filters to a query builder
     *
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */
    public function applyToQuery(Builder $query, array $filters): Builder
    {
        foreach ($filters as $column => $conditions) {
            $query = $this->applyFilterConditions($query, $column, $conditions);
        }

        return $query;
    }

    /**
     * Apply filter conditions to query
     *
     * @param Builder $query
     * @param string $column
     * @param mixed $conditions
     * @return Builder
     */
    protected function applyFilterConditions(Builder $query, string $column, mixed $conditions): Builder
    {
        if (is_array($conditions)) {
            // Handle different filter operators
            if (isset($conditions['operator'])) {
                $operator = $conditions['operator'];
                $value = $conditions['value'] ?? null;

                return match ($operator) {
                    'equals' => $query->where($column, '=', $value),
                    'not_equals' => $query->where($column, '!=', $value),
                    'greater_than' => $query->where($column, '>', $value),
                    'greater_than_or_equal' => $query->where($column, '>=', $value),
                    'less_than' => $query->where($column, '<', $value),
                    'less_than_or_equal' => $query->where($column, '<=', $value),
                    'in' => $query->whereIn($column, (array)$value),
                    'not_in' => $query->whereNotIn($column, (array)$value),
                    'like' => $query->where($column, 'like', "%{$value}%"),
                    'starts_with' => $query->where($column, 'like', "{$value}%"),
                    'ends_with' => $query->where($column, 'like', "%{$value}"),
                    'is_null' => $query->whereNull($column),
                    'is_not_null' => $query->whereNotNull($column),
                    'between' => $this->applyBetweenFilter($query, $column, $value),
                    default => $query,
                };
            }

            // Handle array of values (IN operator)
            if (!empty($conditions)) {
                return $query->whereIn($column, $conditions);
            }
        } else {
            // Single value equality
            return $query->where($column, $conditions);
        }

        return $query;
    }

    /**
     * Apply between filter
     *
     * @param Builder $query
     * @param string $column
     * @param mixed $value
     * @return Builder
     */
    protected function applyBetweenFilter(Builder $query, string $column, mixed $value): Builder
    {
        if (is_array($value) && count($value) === 2) {
            return $query->whereBetween($column, [$value[0], $value[1]]);
        }

        return $query;
    }

    /**
     * Filter collection by conditions
     *
     * @param Collection $collection
     * @param array $filters
     * @return Collection
     */
    public function applyToCollection(Collection $collection, array $filters): Collection
    {
        foreach ($filters as $column => $conditions) {
            $collection = $this->filterCollectionColumn($collection, $column, $conditions);
        }

        return $collection;
    }

    /**
     * Filter collection column
     *
     * @param Collection $collection
     * @param string $column
     * @param mixed $conditions
     * @return Collection
     */
    protected function filterCollectionColumn(Collection $collection, string $column, mixed $conditions): Collection
    {
        if (is_array($conditions) && isset($conditions['operator'])) {
            $operator = $conditions['operator'];
            $value = $conditions['value'] ?? null;

            return match ($operator) {
                'equals' => $collection->where($column, $value),
                'not_equals' => $collection->where($column, '!=', $value),
                'greater_than' => $collection->where($column, '>', $value),
                'less_than' => $collection->where($column, '<', $value),
                'in' => $collection->whereIn($column, (array)$value),
                'like' => $collection->filter(fn($item) =>
                    str_contains((string)($item[$column] ?? ''), $value)
                ),
                default => $collection,
            };
        }

        // If array of values, use IN filter
        if (is_array($conditions)) {
            return $collection->whereIn($column, $conditions);
        }

        // Single value equality
        return $collection->where($column, $conditions);
    }

    /**
     * Validate filter structure
     *
     * @param array $filters
     * @return bool
     */
    public function isValid(array $filters): bool
    {
        foreach ($filters as $column => $conditions) {
            if (!is_string($column)) {
                return false;
            }

            if (is_array($conditions) && isset($conditions['operator'])) {
                $validOperators = [
                    'equals', 'not_equals', 'greater_than', 'greater_than_or_equal',
                    'less_than', 'less_than_or_equal', 'in', 'not_in', 'like',
                    'starts_with', 'ends_with', 'is_null', 'is_not_null', 'between'
                ];

                if (!in_array($conditions['operator'], $validOperators)) {
                    return false;
                }
            }
        }

        return true;
    }
}
