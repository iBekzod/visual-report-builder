<?php

namespace Ibekzod\VisualReportBuilder\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class ReportBuilder
{
    protected QueryBuilder $queryBuilder;
    protected PivotEngine $pivotEngine;
    protected DataSourceManager $dataSourceManager;
    protected ExporterFactory $exporterFactory;
    protected FilterManager $filterManager;
    protected AggregateCalculator $aggregateCalculator;

    public function __construct(
        QueryBuilder $queryBuilder,
        PivotEngine $pivotEngine,
        DataSourceManager $dataSourceManager,
        ExporterFactory $exporterFactory,
        FilterManager $filterManager,
        AggregateCalculator $aggregateCalculator
    ) {
        $this->queryBuilder = $queryBuilder;
        $this->pivotEngine = $pivotEngine;
        $this->dataSourceManager = $dataSourceManager;
        $this->exporterFactory = $exporterFactory;
        $this->filterManager = $filterManager;
        $this->aggregateCalculator = $aggregateCalculator;
    }

    /**
     * Execute report with configuration
     *
     * @param array $config
     * @return array
     */
    public function execute(array $config): array
    {
        // Validate configuration
        $this->validateConfig($config);

        // Get model
        $model = $config['model'] ?? null;

        if (!$model || !$this->dataSourceManager->isValidModel($model)) {
            throw new InvalidArgumentException("Invalid or missing model in configuration");
        }

        // Build and execute query
        $query = $this->queryBuilder->build($config, $model);
        $results = $query->get();

        // Check if pivot is needed
        $rowDims = $config['row_dimensions'] ?? [];
        $colDims = $config['column_dimensions'] ?? [];

        if (!empty($rowDims) || !empty($colDims)) {
            // Build pivot table
            return $this->pivotEngine->build($results, $config);
        }

        // Return raw results
        return [
            'data' => $results->toArray(),
            'count' => $results->count(),
            'metadata' => [
                'row_dimensions' => $rowDims,
                'column_dimensions' => $colDims,
                'metrics' => $config['metrics'] ?? [],
            ],
        ];
    }

    /**
     * Get metadata for a model (available dimensions and metrics)
     *
     * @param string $model
     * @return array
     */
    public function getMetadata(string $model): array
    {
        if (!$this->dataSourceManager->isValidModel($model)) {
            throw new InvalidArgumentException("Invalid model: {$model}");
        }

        return $this->dataSourceManager->getModelMetadata($model);
    }

    /**
     * Get available dimensions for a model
     *
     * @param string $model
     * @return array
     */
    public function getDimensions(string $model): array
    {
        $metadata = $this->getMetadata($model);
        return $metadata['dimensions'] ?? [];
    }

    /**
     * Get available metrics for a model
     *
     * @param string $model
     * @return array
     */
    public function getMetrics(string $model): array
    {
        $metadata = $this->getMetadata($model);
        return $metadata['metrics'] ?? [];
    }

    /**
     * Get all available models
     *
     * @return array
     */
    public function getAvailableModels(): array
    {
        return $this->dataSourceManager->getAvailableModels();
    }

    /**
     * Export report data
     *
     * @param array $data
     * @param string $format
     * @param array $config
     * @return string
     */
    public function export(array $data, string $format, array $config = []): string
    {
        if (!$this->exporterFactory->isEnabled($format)) {
            throw new InvalidArgumentException("Exporter format '{$format}' is not enabled");
        }

        $exporter = $this->exporterFactory->create($format);

        return $exporter->export($data, $config);
    }

    /**
     * Export report with file download
     *
     * @param array $data
     * @param string $format
     * @param string $filename
     * @param array $config
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportAsFile(array $data, string $format, string $filename, array $config = [])
    {
        $exporter = $this->exporterFactory->create($format);

        return $exporter->exportAsFile($data, $filename, $config);
    }

    /**
     * Validate report configuration
     *
     * @param array $config
     * @return bool
     */
    protected function validateConfig(array $config): bool
    {
        if (empty($config['model'])) {
            throw new InvalidArgumentException("Report configuration must include 'model' field");
        }

        // Validate filters if provided
        if (!empty($config['filters']) && !$this->filterManager->isValid($config['filters'])) {
            throw new InvalidArgumentException("Invalid filter configuration");
        }

        return true;
    }

    /**
     * Build report fluently
     *
     * @param string $model
     * @return ReportBuilderFluent
     */
    public static function fluent(string $model): ReportBuilderFluent
    {
        return new ReportBuilderFluent($model);
    }

    /**
     * Get aggregate calculator
     *
     * @return AggregateCalculator
     */
    public function getAggregateCalculator(): AggregateCalculator
    {
        return $this->aggregateCalculator;
    }

    /**
     * Get query builder
     *
     * @return QueryBuilder
     */
    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    /**
     * Get pivot engine
     *
     * @return PivotEngine
     */
    public function getPivotEngine(): PivotEngine
    {
        return $this->pivotEngine;
    }

    /**
     * Get exporter factory
     *
     * @return ExporterFactory
     */
    public function getExporterFactory(): ExporterFactory
    {
        return $this->exporterFactory;
    }
}
