<?php

namespace Ibekzod\VisualReportBuilder\Exporters;

use Barryvdh\DomPDF\Facade\Pdf;

class PDFExporter extends BaseExporter
{
    /**
     * Export data as PDF
     *
     * @param array $data
     * @param array $config
     * @return string
     */
    public function export(array $data, array $config = []): string
    {
        // Determine if data is from pivot or raw query
        $isPivot = isset($data['row_headers']) || isset($data['column_headers']);

        if ($isPivot) {
            $rows = $this->flattenPivotData($data);
        } else {
            $rows = $this->flattenRawData($data);
        }

        // Generate HTML table
        $html = $this->generateHtmlTable($rows, $config);

        // Generate PDF
        $pdf = Pdf::loadHTML($html);

        // Set options
        $pdf->setPaper('A4', $config['orientation'] ?? 'portrait');

        return $pdf->output();
    }

    /**
     * Export as downloadable PDF file
     *
     * @param array $data
     * @param string $filename
     * @param array $config
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportAsFile(array $data, string $filename, array $config = [])
    {
        $filename = str_ends_with($filename, '.pdf') ? $filename : $filename . '.pdf';

        // Determine if data is from pivot or raw query
        $isPivot = isset($data['row_headers']) || isset($data['column_headers']);

        if ($isPivot) {
            $rows = $this->flattenPivotData($data);
        } else {
            $rows = $this->flattenRawData($data);
        }

        // Generate HTML table
        $html = $this->generateHtmlTable($rows, $config);

        // Generate PDF
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', $config['orientation'] ?? 'portrait');

        return $pdf->download($filename);
    }

    /**
     * Generate HTML table from data
     *
     * @param array $rows
     * @param array $config
     * @return string
     */
    protected function generateHtmlTable(array $rows, array $config = []): string
    {
        $title = $config['title'] ?? 'Report';
        $styles = $this->generateStyles();

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{$title}</title>
    <style>
        {$styles}
    </style>
</head>
<body>
    <h1>{$title}</h1>
    <table class="report-table">
        <tbody>
HTML;

        foreach ($rows as $index => $row) {
            $class = $index === 0 ? 'header' : '';
            $html .= "<tr class='{$class}'>";

            foreach ($row as $cell) {
                $tag = $index === 0 ? 'th' : 'td';
                $html .= "<{$tag}>" . htmlspecialchars((string)$cell) . "</{$tag}>";
            }

            $html .= "</tr>";
        }

        $html .= <<<HTML
        </tbody>
    </table>
</body>
</html>
HTML;

        return $html;
    }

    /**
     * Generate CSS styles
     *
     * @return string
     */
    protected function generateStyles(): string
    {
        return <<<CSS
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .report-table tr.header {
            background-color: #f5f5f5;
            font-weight: bold;
        }

        .report-table th {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
            background-color: #f5f5f5;
        }

        .report-table td {
            padding: 10px 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        .report-table tr:nth-child(even) {
            background-color: #fafafa;
        }

        .report-table tr:hover {
            background-color: #f0f0f0;
        }
CSS;
    }

    /**
     * Get MIME type
     *
     * @return string
     */
    public function getMimeType(): string
    {
        return 'application/pdf';
    }

    /**
     * Get file extension
     *
     * @return string
     */
    public function getExtension(): string
    {
        return 'pdf';
    }
}
