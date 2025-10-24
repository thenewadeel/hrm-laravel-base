<?php
// app/Traits/ReadsCsvData.php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait ReadsCsvData
{
    protected function readCsvData(string $filePath): array
    {
        $fullPath = database_path("data/{$filePath}");

        if (!file_exists($fullPath)) {
            throw new \Exception("CSV file not found: {$fullPath}");
        }

        $data = [];
        $header = null;

        if (($handle = fopen($fullPath, 'r')) !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                if (!$header) {
                    $header = $row;
                    continue;
                }

                if (count($row) === count($header)) {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }

        return $data;
    }

    protected function readJsonData(string $filePath): array
    {
        $fullPath = database_path("data/{$filePath}");

        if (!file_exists($fullPath)) {
            throw new \Exception("JSON file not found: {$fullPath}");
        }

        return json_decode(file_get_contents($fullPath), true);
    }
}
