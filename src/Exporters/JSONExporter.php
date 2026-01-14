<?php

namespace Ibekzod\VisualReportBuilder\Exporters;

class JSONExporter extends BaseExporter
{
    /**
     * Export data as JSON
     *
     * @param array $data
     * @param array $config
     * @return string
     */
    public function export(array $data, array $config = []): string
    {
        $pretty = $config['pretty'] ?? true;
        $options = $pretty ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES : 0;

        return json_encode($data, $options);
    }

    /**
     * Get MIME type
     *
     * @return string
     */
    public function getMimeType(): string
    {
        return 'application/json';
    }

    /**
     * Get file extension
     *
     * @return string
     */
    public function getExtension(): string
    {
        return 'json';
    }
}
