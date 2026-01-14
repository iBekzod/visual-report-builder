<?php

namespace Ibekzod\VisualReportBuilder\Exporters;

use Ibekzod\VisualReportBuilder\Contracts\ExporterContract;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class BaseExporter implements ExporterContract
{
    /**
     * Format values for export
     *
     * @param mixed $value
     * @return mixed
     */
    protected function formatValue(mixed $value): mixed
    {
        if ($value === null) {
            return '';
        }

        if (is_array($value)) {
            return implode(', ', $value);
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        return $value;
    }

    /**
     * Flatten pivot table data for export
     *
     * @param array $pivotData
     * @return array
     */
    protected function flattenPivotData(array $pivotData): array
    {
        $flattened = [];

        $rowHeaders = $pivotData['row_headers'] ?? [];
        $colHeaders = $pivotData['column_headers'] ?? [];
        $dataMatrix = $pivotData['data_matrix'] ?? [];
        $rowTotals = $pivotData['row_totals'] ?? [];
        $colTotals = $pivotData['column_totals'] ?? [];
        $grandTotal = $pivotData['grand_total'] ?? [];

        // Build header row
        $headerRow = ['Dimension'];

        foreach ($colHeaders as $colHeader) {
            $headerRow[] = implode(' - ', (array)$colHeader);
        }

        $headerRow[] = 'Total';
        $flattened[] = $headerRow;

        // Build data rows
        for ($i = 0; $i < count($rowHeaders); $i++) {
            $row = [implode(' - ', (array)($rowHeaders[$i] ?? []))];

            for ($j = 0; $j < count($colHeaders); $j++) {
                $cellData = $dataMatrix[$i][$j] ?? [];
                // For now, just use the first metric value
                $value = array_values($cellData)[0] ?? '';
                $row[] = $this->formatValue($value);
            }

            // Add row total
            $rowTotal = $rowTotals[$i] ?? [];
            $totalValue = array_values($rowTotal)[0] ?? '';
            $row[] = $this->formatValue($totalValue);

            $flattened[] = $row;
        }

        // Add grand total row
        if (!empty($colTotals)) {
            $totalRow = ['Total'];

            foreach ($colTotals as $colTotal) {
                $value = array_values($colTotal)[0] ?? '';
                $totalRow[] = $this->formatValue($value);
            }

            $grandTotalValue = array_values($grandTotal)[0] ?? '';
            $totalRow[] = $this->formatValue($grandTotalValue);

            $flattened[] = $totalRow;
        }

        return $flattened;
    }

    /**
     * Flatten raw data for export
     *
     * @param array $data
     * @return array
     */
    protected function flattenRawData(array $data): array
    {
        if (!isset($data['data'])) {
            return $data;
        }

        $flattened = [];

        // Get column headers
        if (!empty($data['data'])) {
            $firstRow = $data['data'][0];
            if (is_array($firstRow)) {
                $flattened[] = array_keys($firstRow);
            }
        }

        // Add data rows
        foreach ($data['data'] ?? [] as $row) {
            if (is_array($row)) {
                $flattened[] = array_values($row);
            } else {
                $flattened[] = [$row];
            }
        }

        return $flattened;
    }

    /**
     * Create streaming response for download
     *
     * @param string $content
     * @param string $filename
     * @param string $mimeType
     * @return StreamedResponse
     */
    protected function createStreamedResponse(string $content, string $filename, string $mimeType): StreamedResponse
    {
        return response()->streamDownload(function () use ($content) {
            echo $content;
        }, $filename, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Export as file with streaming response
     *
     * @param array $data
     * @param string $filename
     * @param array $config
     * @return StreamedResponse
     */
    public function exportAsFile(array $data, string $filename, array $config = [])
    {
        $content = $this->export($data, $config);

        return $this->createStreamedResponse($content, $filename, $this->getMimeType());
    }
}
