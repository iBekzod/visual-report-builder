<?php

namespace Ibekzod\VisualReportBuilder\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array execute(array $config)
 * @method static array getMetadata(string $model)
 * @method static array getDimensions(string $model)
 * @method static array getMetrics(string $model)
 * @method static string export(array $data, array $config, string $format)
 * @method static \Ibekzod\VisualReportBuilder\Services\ReportBuilder withModel(string $model)
 * @method static \Ibekzod\VisualReportBuilder\Services\ReportBuilder withDimensions(array $dimensions)
 * @method static \Ibekzod\VisualReportBuilder\Services\ReportBuilder withMetrics(array $metrics)
 * @method static \Ibekzod\VisualReportBuilder\Services\ReportBuilder withFilters(array $filters)
 *
 * @see \Ibekzod\VisualReportBuilder\Services\ReportBuilder
 */
class VisualReportBuilder extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'visual-report-builder';
    }
}
