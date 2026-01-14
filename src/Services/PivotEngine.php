<?php

namespace Ibekzod\VisualReportBuilder\Services;

use Illuminate\Support\Collection;

class PivotEngine
{
    protected AggregateCalculator $aggregateCalculator;

    public function __construct()
    {
        $this->aggregateCalculator = new AggregateCalculator();
    }

    /**
     * Build multi-dimensional pivot table
     *
     * @param Collection|array $data
     * @param array $config
     * @return array
     */
    public function build($data, array $config): array
    {
        if (is_array($data)) {
            $data = collect($data);
        }

        $rowDims = $config['row_dimensions'] ?? [];
        $colDims = $config['column_dimensions'] ?? [];
        $metrics = $config['metrics'] ?? [];

        if ($data->isEmpty()) {
            return [
                'row_headers' => [],
                'column_headers' => [],
                'data_matrix' => [],
                'row_totals' => [],
                'column_totals' => [],
                'grand_total' => [],
            ];
        }

        // Build headers
        $rowHeaders = $this->buildRowHeaders($data, $rowDims);
        $colHeaders = $this->buildColumnHeaders($data, $colDims);

        // Build data matrix
        $matrix = $this->buildMatrix($data, $rowHeaders, $colHeaders, $rowDims, $colDims, $metrics);

        // Calculate totals
        $rowTotals = $config['include_totals'] !== false ? $this->calculateRowTotals($matrix, $metrics) : [];
        $colTotals = $config['include_totals'] !== false ? $this->calculateColumnTotals($matrix, $metrics) : [];
        $grandTotal = $config['include_totals'] !== false ? $this->calculateGrandTotal($data, $metrics) : [];

        return [
            'row_headers' => $rowHeaders,
            'column_headers' => $colHeaders,
            'data_matrix' => $matrix,
            'row_totals' => $rowTotals,
            'column_totals' => $colTotals,
            'grand_total' => $grandTotal,
        ];
    }

    /**
     * Build row headers from dimensions
     *
     * @param Collection $data
     * @param array $rowDims
     * @return array
     */
    protected function buildRowHeaders(Collection $data, array $rowDims): array
    {
        if (empty($rowDims)) {
            return [];
        }

        $uniqueRows = [];

        foreach ($data as $row) {
            $rowKey = [];
            foreach ($rowDims as $dim) {
                $rowKey[] = $row[$dim] ?? '';
            }

            $keyString = implode('|', $rowKey);

            if (!isset($uniqueRows[$keyString])) {
                $uniqueRows[$keyString] = $rowKey;
            }
        }

        return array_values($uniqueRows);
    }

    /**
     * Build column headers from dimensions
     *
     * @param Collection $data
     * @param array $colDims
     * @return array
     */
    protected function buildColumnHeaders(Collection $data, array $colDims): array
    {
        if (empty($colDims)) {
            return [];
        }

        $uniqueCols = [];

        foreach ($data as $row) {
            $colKey = [];
            foreach ($colDims as $dim) {
                $colKey[] = $row[$dim] ?? '';
            }

            $keyString = implode('|', $colKey);

            if (!isset($uniqueCols[$keyString])) {
                $uniqueCols[$keyString] = $colKey;
            }
        }

        return array_values($uniqueCols);
    }

    /**
     * Build data matrix
     *
     * @param Collection $data
     * @param array $rowHeaders
     * @param array $colHeaders
     * @param array $rowDims
     * @param array $colDims
     * @param array $metrics
     * @return array
     */
    protected function buildMatrix(Collection $data, array $rowHeaders, array $colHeaders, array $rowDims, array $colDims, array $metrics): array
    {
        $matrix = [];

        foreach ($rowHeaders as $rowIndex => $rowHeader) {
            $matrix[$rowIndex] = [];

            foreach ($colHeaders as $colIndex => $colHeader) {
                // Filter data for this cell
                $cellData = $this->filterForCell($data, $rowDims, $rowHeader, $colDims, $colHeader);

                // Calculate metrics for this cell
                $cellMetrics = [];
                foreach ($metrics as $metric) {
                    $column = $metric['column'] ?? null;
                    $aggregate = $metric['aggregate'] ?? 'sum';
                    $alias = $metric['alias'] ?? "{$column}_{$aggregate}";

                    if ($column) {
                        $cellMetrics[$alias] = $this->aggregateCalculator->calculate($cellData, $column, $aggregate);
                    }
                }

                $matrix[$rowIndex][$colIndex] = $cellMetrics;
            }
        }

        return $matrix;
    }

    /**
     * Filter data for a specific cell
     *
     * @param Collection $data
     * @param array $rowDims
     * @param array $rowHeader
     * @param array $colDims
     * @param array $colHeader
     * @return Collection
     */
    protected function filterForCell(Collection $data, array $rowDims, array $rowHeader, array $colDims, array $colHeader): Collection
    {
        $filtered = $data;

        // Filter by row dimensions
        foreach ($rowDims as $index => $dim) {
            $value = $rowHeader[$index] ?? null;
            $filtered = $filtered->where($dim, $value);
        }

        // Filter by column dimensions
        foreach ($colDims as $index => $dim) {
            $value = $colHeader[$index] ?? null;
            $filtered = $filtered->where($dim, $value);
        }

        return $filtered;
    }

    /**
     * Calculate row totals
     *
     * @param array $matrix
     * @param array $metrics
     * @return array
     */
    protected function calculateRowTotals(array $matrix, array $metrics): array
    {
        $totals = [];

        foreach ($matrix as $rowIndex => $row) {
            $totals[$rowIndex] = [];

            foreach ($metrics as $metric) {
                $alias = $metric['alias'] ?? null;

                if ($alias) {
                    $values = array_column($row, $alias);
                    $aggregate = $metric['aggregate'] ?? 'sum';

                    $totals[$rowIndex][$alias] = match ($aggregate) {
                        'sum' => array_sum($values),
                        'avg' => count($values) > 0 ? array_sum($values) / count($values) : 0,
                        'max' => max($values),
                        'min' => min($values),
                        'count' => count($values),
                        default => null,
                    };
                }
            }
        }

        return $totals;
    }

    /**
     * Calculate column totals
     *
     * @param array $matrix
     * @param array $metrics
     * @return array
     */
    protected function calculateColumnTotals(array $matrix, array $metrics): array
    {
        $totals = [];

        $numCols = count($matrix[0] ?? []);

        for ($colIndex = 0; $colIndex < $numCols; $colIndex++) {
            $totals[$colIndex] = [];

            foreach ($metrics as $metric) {
                $alias = $metric['alias'] ?? null;

                if ($alias) {
                    $values = [];

                    foreach ($matrix as $row) {
                        if (isset($row[$colIndex][$alias])) {
                            $values[] = $row[$colIndex][$alias];
                        }
                    }

                    $aggregate = $metric['aggregate'] ?? 'sum';

                    $totals[$colIndex][$alias] = match ($aggregate) {
                        'sum' => array_sum($values),
                        'avg' => count($values) > 0 ? array_sum($values) / count($values) : 0,
                        'max' => !empty($values) ? max($values) : 0,
                        'min' => !empty($values) ? min($values) : 0,
                        'count' => count($values),
                        default => null,
                    };
                }
            }
        }

        return $totals;
    }

    /**
     * Calculate grand total
     *
     * @param Collection $data
     * @param array $metrics
     * @return array
     */
    protected function calculateGrandTotal(Collection $data, array $metrics): array
    {
        $total = [];

        foreach ($metrics as $metric) {
            $column = $metric['column'] ?? null;
            $aggregate = $metric['aggregate'] ?? 'sum';
            $alias = $metric['alias'] ?? "{$column}_{$aggregate}";

            if ($column) {
                $total[$alias] = $this->aggregateCalculator->calculate($data, $column, $aggregate);
            }
        }

        return $total;
    }
}
