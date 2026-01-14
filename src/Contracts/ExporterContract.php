<?php

namespace Ibekzod\VisualReportBuilder\Contracts;

interface ExporterContract
{
    /**
     * Export data to the specified format
     *
     * @param array $data
     * @param array $config
     * @return string
     */
    public function export(array $data, array $config = []): string;

    /**
     * Export data as downloadable file
     *
     * @param array $data
     * @param string $filename
     * @param array $config
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportAsFile(array $data, string $filename, array $config = []);

    /**
     * Get the MIME type for this format
     *
     * @return string
     */
    public function getMimeType(): string;

    /**
     * Get the file extension for this format
     *
     * @return string
     */
    public function getExtension(): string;
}
