<?php

namespace Ibekzod\VisualReportBuilder\Exporters;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelWriter;

class ExcelExporter extends BaseExporter
{
    /**
     * Export data as Excel
     *
     * @param array $data
     * @param array $config
     * @return string
     */
    public function export(array $data, array $config = []): string
    {
        // Generate CSV first and return as-is
        // For actual Excel file, use exportAsFile method
        $csvExporter = new CSVExporter();
        return $csvExporter->export($data, $config);
    }

    /**
     * Export as downloadable Excel file
     *
     * @param array $data
     * @param string $filename
     * @param array $config
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportAsFile(array $data, string $filename, array $config = [])
    {
        $filename = str_ends_with($filename, '.xlsx') ? $filename : $filename . '.xlsx';

        // Determine if data is from pivot or raw query
        $isPivot = isset($data['row_headers']) || isset($data['column_headers']);

        if ($isPivot) {
            $rows = $this->flattenPivotData($data);
        } else {
            $rows = $this->flattenRawData($data);
        }

        // Create temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'excel');

        try {
            // Use Laravel Excel to export
            return Excel::download(
                new class($rows) {
                    protected $rows;

                    public function __construct($rows)
                    {
                        $this->rows = $rows;
                    }

                    public function collection()
                    {
                        return collect($this->rows);
                    }
                },
                $filename,
                ExcelWriter::XLSX
            );
        } catch (\Exception $e) {
            // Fallback to CSV
            $csvExporter = new CSVExporter();
            $csvFilename = str_replace('.xlsx', '.csv', $filename);
            return $csvExporter->exportAsFile($data, $csvFilename);
        }
    }

    /**
     * Get MIME type
     *
     * @return string
     */
    public function getMimeType(): string
    {
        return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    }

    /**
     * Get file extension
     *
     * @return string
     */
    public function getExtension(): string
    {
        return 'xlsx';
    }
}
