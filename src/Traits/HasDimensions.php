<?php

namespace Ibekzod\VisualReportBuilder\Traits;

/**
 * Trait to add dimension support to a model
 *
 * This trait helps define which columns can be used as dimensions in reports
 */
trait HasDimensions
{
    /**
     * Get dimensions for this model
     *
     * @return array
     */
    public static function getDimensions(): array
    {
        if (method_exists(static::class, 'dimensions')) {
            return static::dimensions();
        }

        return [];
    }

    /**
     * Add dimension configuration
     *
     * Override this method in your model to define custom dimensions:
     *
     * protected static function dimensions(): array
     * {
     *     return [
     *         [
     *             'column' => 'region',
     *             'label' => 'Region',
     *             'type' => 'string',
     *         ],
     *         [
     *             'column' => 'status',
     *             'label' => 'Status',
     *             'type' => 'string',
     *         ],
     *     ];
     * }
     *
     * @return array
     */
    protected static function dimensions(): array
    {
        return [];
    }

    /**
     * Check if column is a valid dimension
     *
     * @param string $column
     * @return bool
     */
    public static function isValidDimension(string $column): bool
    {
        $dimensions = static::getDimensions();

        foreach ($dimensions as $dimension) {
            if ($dimension['column'] === $column) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get dimension by column name
     *
     * @param string $column
     * @return array|null
     */
    public static function getDimension(string $column): ?array
    {
        $dimensions = static::getDimensions();

        foreach ($dimensions as $dimension) {
            if ($dimension['column'] === $column) {
                return $dimension;
            }
        }

        return null;
    }
}
