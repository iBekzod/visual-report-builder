<?php

namespace Ibekzod\VisualReportBuilder\Exporters;

class CSVExporter extends BaseExporter
{
    /**
     * Export data as CSV
     *
     * @param array $data
     * @param array $config
     * @return string
     */
    public function export(array $data, array $config = []): string
    {
        $delimiter = $config['delimiter'] ?? ',';
        $enclosure = $config['enclosure'] ?? '"';

        // Determine if data is from pivot or raw query
        $isPivot = isset($data['row_headers']) || isset($data['column_headers']);

        if ($isPivot) {
            $rows = $this->flattenPivotData($data);
        } else {
            $rows = $this->flattenRawData($data);
        }

        // Generate CSV
        $csv = '';

        foreach ($rows as $row) {
            $line = [];

            foreach ($row as $value) {
                $value = $this->formatValue($value);

                // Escape quotes
                if (is_string($value) && (str_contains($value, $delimiter) || str_contains($value, $enclosure) || str_contains($value, "\n"))) {
                    $value = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $value) . $enclosure;
                }

                $line[] = $value;
            }

            $csv .= implode($delimiter, $line) . "\n";
        }

        return $csv;
    }

    /**
     * Get MIME type
     *
     * @return string
     */
    public function getMimeType(): string
    {
        return 'text/csv';
    }

    /**
     * Get file extension
     *
     * @return string
     */
    public function getExtension(): string
    {
        return 'csv';
    }
}
