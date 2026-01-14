<?php

use Ibekzod\VisualReportBuilder\Facades\VisualReportBuilder;

if (!function_exists('visual_report_builder')) {
    /**
     * Get the Visual Report Builder instance
     *
     * @return \Ibekzod\VisualReportBuilder\Services\ReportBuilder
     */
    function visual_report_builder()
    {
        return app('visual-report-builder');
    }
}

if (!function_exists('execute_report')) {
    /**
     * Execute a report with the given configuration
     *
     * @param array $config
     * @return array
     */
    function execute_report(array $config)
    {
        return visual_report_builder()->execute($config);
    }
}

if (!function_exists('export_report')) {
    /**
     * Export report data to specified format
     *
     * @param array $data
     * @param string $format
     * @param array $config
     * @return string
     */
    function export_report(array $data, string $format, array $config = [])
    {
        return visual_report_builder()->export($data, $format, $config);
    }
}

if (!function_exists('get_report_metadata')) {
    /**
     * Get metadata for a model (dimensions and metrics)
     *
     * @param string $model
     * @return array
     */
    function get_report_metadata(string $model)
    {
        return visual_report_builder()->getMetadata($model);
    }
}

if (!function_exists('get_available_models')) {
    /**
     * Get all available models for reporting
     *
     * @return array
     */
    function get_available_models()
    {
        return visual_report_builder()->getAvailableModels();
    }
}
