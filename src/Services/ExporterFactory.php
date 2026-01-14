<?php

namespace Ibekzod\VisualReportBuilder\Services;

use Ibekzod\VisualReportBuilder\Contracts\ExporterContract;
use Ibekzod\VisualReportBuilder\Exporters\CSVExporter;
use Ibekzod\VisualReportBuilder\Exporters\ExcelExporter;
use Ibekzod\VisualReportBuilder\Exporters\PDFExporter;
use Ibekzod\VisualReportBuilder\Exporters\JSONExporter;
use InvalidArgumentException;

class ExporterFactory
{
    /**
     * Available exporters
     */
    protected array $exporters = [
        'csv' => CSVExporter::class,
        'excel' => ExcelExporter::class,
        'pdf' => PDFExporter::class,
        'json' => JSONExporter::class,
    ];

    /**
     * Create exporter instance
     *
     * @param string $format
     * @return ExporterContract
     */
    public function create(string $format): ExporterContract
    {
        $format = strtolower($format);

        if (!isset($this->exporters[$format])) {
            throw new InvalidArgumentException("Unsupported export format: {$format}");
        }

        $exporterClass = $this->exporters[$format];

        return new $exporterClass();
    }

    /**
     * Check if exporter is enabled
     *
     * @param string $format
     * @return bool
     */
    public function isEnabled(string $format): bool
    {
        $config = config('visual-report-builder.exporters', []);
        return $config[strtolower($format)] ?? false;
    }

    /**
     * Get enabled exporters
     *
     * @return array
     */
    public function getEnabled(): array
    {
        $config = config('visual-report-builder.exporters', []);
        $enabled = [];

        foreach ($config as $format => $isEnabled) {
            if ($isEnabled && isset($this->exporters[$format])) {
                $enabled[] = $format;
            }
        }

        return $enabled;
    }

    /**
     * Register custom exporter
     *
     * @param string $format
     * @param string $class
     * @return void
     */
    public function register(string $format, string $class): void
    {
        if (!is_subclass_of($class, ExporterContract::class)) {
            throw new InvalidArgumentException("Exporter class must implement ExporterContract");
        }

        $this->exporters[strtolower($format)] = $class;
    }

    /**
     * Get all available exporters
     *
     * @return array
     */
    public function getAll(): array
    {
        return array_keys($this->exporters);
    }
}
