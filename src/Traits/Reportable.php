<?php

namespace Ibekzod\VisualReportBuilder\Traits;

trait Reportable
{
    /**
     * Get available dimensions for this model
     *
     * @return array
     */
    public static function getReportableDimensions(): array
    {
        if (method_exists(static::class, 'reportableDimensions')) {
            return static::reportableDimensions();
        }

        // Auto-discover dimensions from non-numeric, non-sensitive columns
        $instance = new static();
        $builder = app('visual-report-builder');
        $metadata = $builder->getMetadata(static::class);

        return $metadata['dimensions'] ?? [];
    }

    /**
     * Get available metrics for this model
     *
     * @return array
     */
    public static function getReportableMetrics(): array
    {
        if (method_exists(static::class, 'reportableMetrics')) {
            return static::reportableMetrics();
        }

        // Auto-discover metrics from numeric columns
        $instance = new static();
        $builder = app('visual-report-builder');
        $metadata = $builder->getMetadata(static::class);

        return $metadata['metrics'] ?? [];
    }

    /**
     * Get full report metadata
     *
     * @return array
     */
    public static function getReportMetadata(): array
    {
        $builder = app('visual-report-builder');
        return $builder->getMetadata(static::class);
    }

    /**
     * Execute a report on this model
     *
     * @param array $config
     * @return array
     */
    public static function executeReport(array $config): array
    {
        $config['model'] = static::class;
        $builder = app('visual-report-builder');
        return $builder->execute($config);
    }

    /**
     * Build a report fluently
     *
     * @return \Ibekzod\VisualReportBuilder\Services\ReportBuilder
     */
    public static function buildReport()
    {
        return app('visual-report-builder')->fluent(static::class);
    }

    /**
     * Get dimensions - can be overridden in model
     *
     * @return array
     */
    protected static function reportableDimensions(): array
    {
        return [];
    }

    /**
     * Get metrics - can be overridden in model
     *
     * @return array
     */
    protected static function reportableMetrics(): array
    {
        return [];
    }
}
