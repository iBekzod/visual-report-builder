<?php

namespace Ibekzod\VisualReportBuilder\Services;

use Illuminate\Support\Collection;

class AggregateCalculator
{
    /**
     * Available aggregate functions
     */
    protected array $aggregates = [
        'sum' => 'sum',
        'avg' => 'average',
        'min' => 'min',
        'max' => 'max',
        'count' => 'count',
        'value' => 'raw',
    ];

    /**
     * Calculate aggregate for a column with a specific function
     *
     * @param Collection $collection
     * @param string $column
     * @param string $aggregate
     * @return mixed
     */
    public function calculate(Collection $collection, string $column, string $aggregate)
    {
        if (!isset($this->aggregates[$aggregate])) {
            throw new \InvalidArgumentException("Unsupported aggregate function: {$aggregate}");
        }

        return match ($aggregate) {
            'sum' => $this->calculateSum($collection, $column),
            'avg' => $this->calculateAverage($collection, $column),
            'min' => $this->calculateMin($collection, $column),
            'max' => $this->calculateMax($collection, $column),
            'count' => $this->calculateCount($collection, $column),
            'value' => $this->calculateValue($collection, $column),
            default => null,
        };
    }

    /**
     * Calculate sum of values
     */
    protected function calculateSum(Collection $collection, string $column): float|int
    {
        return $collection->sum($column) ?? 0;
    }

    /**
     * Calculate average of values
     */
    protected function calculateAverage(Collection $collection, string $column): float
    {
        $values = $collection->pluck($column)->filter();
        return $values->count() > 0 ? $values->sum() / $values->count() : 0;
    }

    /**
     * Calculate minimum value
     */
    protected function calculateMin(Collection $collection, string $column): mixed
    {
        return $collection->min($column);
    }

    /**
     * Calculate maximum value
     */
    protected function calculateMax(Collection $collection, string $column): mixed
    {
        return $collection->max($column);
    }

    /**
     * Calculate count of records
     */
    protected function calculateCount(Collection $collection, string $column = null): int
    {
        if ($column) {
            return $collection->where($column, '!=', null)->count();
        }
        return $collection->count();
    }

    /**
     * Get raw value (first value)
     */
    protected function calculateValue(Collection $collection, string $column): mixed
    {
        return $collection->first($column) ?? $collection->first()[$column] ?? null;
    }

    /**
     * Get all available aggregates
     */
    public function getAvailableAggregates(): array
    {
        return array_keys($this->aggregates);
    }

    /**
     * Format aggregated value for display
     *
     * @param mixed $value
     * @param string $type
     * @return string
     */
    public function formatValue(mixed $value, string $type = 'default'): string
    {
        if ($value === null) {
            return '-';
        }

        return match ($type) {
            'currency' => $this->formatCurrency($value),
            'percentage' => $this->formatPercentage($value),
            'decimal' => number_format($value, 2),
            'integer' => (string)(int)$value,
            default => (string)$value,
        };
    }

    /**
     * Format as currency
     */
    protected function formatCurrency(mixed $value): string
    {
        return '$' . number_format($value, 2);
    }

    /**
     * Format as percentage
     */
    protected function formatPercentage(mixed $value): string
    {
        return number_format($value, 2) . '%';
    }
}
