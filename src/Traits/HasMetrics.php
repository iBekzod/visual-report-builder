<?php

namespace Ibekzod\VisualReportBuilder\Traits;

/**
 * Trait to add metric support to a model
 *
 * This trait helps define which columns can be used as metrics in reports
 */
trait HasMetrics
{
    /**
     * Get metrics for this model
     *
     * @return array
     */
    public static function getMetrics(): array
    {
        if (method_exists(static::class, 'metrics')) {
            return static::metrics();
        }

        return [];
    }

    /**
     * Add metric configuration
     *
     * Override this method in your model to define custom metrics:
     *
     * protected static function metrics(): array
     * {
     *     return [
     *         [
     *             'column' => 'amount',
     *             'label' => 'Total Amount',
     *             'type' => 'decimal',
     *             'default_aggregate' => 'sum',
     *         ],
     *         [
     *             'column' => 'quantity',
     *             'label' => 'Quantity',
     *             'type' => 'integer',
     *             'default_aggregate' => 'sum',
     *         ],
     *     ];
     * }
     *
     * @return array
     */
    protected static function metrics(): array
    {
        return [];
    }

    /**
     * Check if column is a valid metric
     *
     * @param string $column
     * @return bool
     */
    public static function isValidMetric(string $column): bool
    {
        $metrics = static::getMetrics();

        foreach ($metrics as $metric) {
            if ($metric['column'] === $column) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get metric by column name
     *
     * @param string $column
     * @return array|null
     */
    public static function getMetric(string $column): ?array
    {
        $metrics = static::getMetrics();

        foreach ($metrics as $metric) {
            if ($metric['column'] === $column) {
                return $metric;
            }
        }

        return null;
    }

    /**
     * Get default aggregate for metric
     *
     * @param string $column
     * @return string
     */
    public static function getDefaultAggregate(string $column): string
    {
        $metric = static::getMetric($column);
        return $metric['default_aggregate'] ?? 'sum';
    }

    /**
     * Get all available aggregates for a metric
     *
     * @return array
     */
    public static function getAvailableAggregates(): array
    {
        return ['sum', 'avg', 'min', 'max', 'count', 'count_distinct', 'value'];
    }
}
