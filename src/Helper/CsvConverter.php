<?php

namespace App\Helper;

class CsvConverter
{
    private string $filePath;

    public function setFilePath(string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function convert($hasHeader = false, $delimiter = ',', $enclosure = '"'): array
    {
        if (!file_exists($this->filePath) || !is_readable($this->filePath)) {
            return [];
        }

        $header = [];
        $data = [];

        if (($handle = fopen($this->filePath, 'r'))) {
            while ($row = fgetcsv($handle, 0, $delimiter, $enclosure)) {
                if (empty(reset($row))) {
                    break;
                }

                if (empty($header) && $hasHeader) {
                    $header = $row;
                } elseif ($header) {
                    $data[] = array_combine($header, $row);
                } else {
                    $data[] = $row;
                }
            }
            fclose($handle);
        }
        return $data;
    }
}