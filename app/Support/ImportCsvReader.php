<?php

namespace App\Support;

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

/**
 * Single place deciding how a text-based import file (csv/tsv/txt) is read,
 * shared by ImportGetSummary (headers) and ImportProspects (rows) so both
 * always parse the file the same way. When the user did not choose a
 * delimiter, it is detected from the file itself (",", ";", tab or "|").
 */
class ImportCsvReader
{
    public const EXTENSIONS = ['csv', 'tsv', 'txt'];

    public static function supports(string $extension): bool
    {
        return in_array(strtolower($extension), self::EXTENSIONS, true);
    }

    public static function make($import, string $filepath)
    {
        $reader = ReaderEntityFactory::createCSVReader();

        $delimiter = $import->field_delimiter
            ? ($import->field_delimiter === 'tab' ? "\t" : $import->field_delimiter)
            : self::detectDelimiter($filepath);

        $reader->setFieldDelimiter($delimiter);

        if ($import->field_enclosure) {
            $reader->setFieldEnclosure($import->field_enclosure);
        }

        return $reader;
    }

    public static function detectDelimiter(string $filepath): string
    {
        $handle = @fopen($filepath, 'r');
        if (!$handle) {
            return ',';
        }

        $line = '';
        while (($candidate = fgets($handle)) !== false) {
            if (trim($candidate) !== '') {
                $line = $candidate;
                break;
            }
        }
        fclose($handle);

        $best = ',';
        $bestCount = 0;

        foreach ([',', ';', "\t", '|'] as $delimiter) {
            $count = substr_count($line, $delimiter);
            if ($count > $bestCount) {
                $best = $delimiter;
                $bestCount = $count;
            }
        }

        return $best;
    }
}
