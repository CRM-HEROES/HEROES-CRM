<?php

namespace App\Support\Import;

use XMLReader;

/**
 * Streaming XLSX reader that returns exactly the values Box Spout returns
 * (same string/int/float/bool/DateTime typing, same row padding, same empty
 * row skipping) but about 10x faster: Spout expands every <c> into a DOM
 * node, which takes ~30 s on a 11 000-row Google Sheets export where the
 * XML itself is only ~10 MB.
 *
 * It exposes the small part of Spout's reader API that ImportProspects
 * uses (open / getSheetIterator / close, sheets with getName() and
 * getRowIterator(), rows with getCells()). The rules below are ports of
 * Spout's RowIterator, CellValueFormatter, StyleManager and
 * SharedStringsManager — keep them in sync, and see verifyAgainstSpout().
 */
class FastXlsxReader
{
    const NS_REL = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

    const BUILTIN_DATE_FORMATS = [14, 15, 16, 17, 18, 19, 20, 21, 22, 45, 46, 47];

    protected $path;
    protected $sheets = [];
    protected $sharedStrings = null;
    protected $styles = null;
    protected $use1904 = false;
    protected $sharedStringsPath = null;
    protected $stylesPath = null;

    public function open($path)
    {
        $this->path = $path;
        $this->sheets = [];
        $this->sharedStrings = null;
        $this->styles = null;

        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \RuntimeException('FastXlsxReader: cannot open ' . $path);
        }

        $rels = [];
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $zip->close();

        if ($relsXml === false || $workbookXml === false) {
            throw new \RuntimeException('FastXlsxReader: not a regular xlsx workbook');
        }

        $doc = new \SimpleXMLElement($relsXml);
        foreach ($doc->children() as $rel) {
            $target = (string) $rel['Target'];
            $target = strpos($target, '/xl/') === 0 ? ltrim($target, '/') : 'xl/' . ltrim($target, '/');
            $rels[(string) $rel['Id']] = $target;

            $type = (string) $rel['Type'];
            if (substr($type, -14) === '/sharedStrings') {
                $this->sharedStringsPath = $target;
            } elseif (substr($type, -7) === '/styles') {
                $this->stylesPath = $target;
            }
        }

        $wb = new \SimpleXMLElement($workbookXml);
        $wb->registerXPathNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $pr = $wb->xpath('//m:workbookPr');
        if ($pr) {
            $this->use1904 = filter_var((string) $pr[0]['date1904'], FILTER_VALIDATE_BOOLEAN);
        }

        foreach ($wb->xpath('//m:sheets/m:sheet') as $sheet) {
            $rid = (string) $sheet->attributes(self::NS_REL)['id'];
            if (!isset($rels[$rid])) {
                throw new \RuntimeException('FastXlsxReader: sheet without relationship');
            }

            $this->sheets[] = new FastXlsxSheet($this, (string) $sheet['name'], $rels[$rid]);
        }
    }

    public function getSheetIterator()
    {
        foreach ($this->sheets as $sheet) {
            yield $sheet;
        }
    }

    public function close()
    {
        $this->sharedStrings = null;
        $this->styles = null;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Rows of one worksheet, as FastXlsxRow objects.
     */
    public function readRows(string $internalPath)
    {
        $this->loadSharedStrings();
        $this->loadStyles();

        $xml = new XMLReader();
        if (!$xml->open('zip://' . $this->path . '#' . $internalPath, null, LIBXML_NONET | LIBXML_PARSEHUGE)) {
            throw new \RuntimeException('FastXlsxReader: cannot read ' . $internalPath);
        }

        $numColumns = 0;
        $lastRowIndex = 0;

        while ($xml->read()) {
            if ($xml->nodeType !== XMLReader::ELEMENT) {
                continue;
            }

            if ($xml->localName === 'dimension') {
                if (preg_match('/[A-Z]+\d+:([A-Z]+\d+)/', (string) $xml->getAttribute('ref'), $m)) {
                    $numColumns = self::columnIndex($m[1]) + 1;
                }
                continue;
            }

            if ($xml->localName !== 'row') {
                continue;
            }

            $rowIndexAttr = $xml->getAttribute('r');
            $lastRowIndex = $rowIndexAttr !== null ? (int) $rowIndexAttr : $lastRowIndex + 1;

            $columnsForRow = $numColumns;
            $spans = $xml->getAttribute('spans');
            if ($spans) {
                [, $columnsForRow] = explode(':', $spans);
                $columnsForRow = (int) $columnsForRow;
            }

            $cells = $columnsForRow > 0 ? array_fill(0, $columnsForRow, '') : [];
            $lastColumn = -1;

            if (!$xml->isEmptyElement) {
                $rowDepth = $xml->depth;

                while ($xml->read()) {
                    if ($xml->nodeType === XMLReader::END_ELEMENT && $xml->depth === $rowDepth) {
                        break;
                    }

                    if ($xml->nodeType !== XMLReader::ELEMENT || $xml->localName !== 'c') {
                        continue;
                    }

                    $ref = $xml->getAttribute('r');
                    $column = $ref !== null ? self::columnIndex($ref) : $lastColumn + 1;
                    $lastColumn = $column;

                    $cells[$column] = $this->readCell($xml);
                }
            }

            // Spout skips rows whose cells are all empty.
            $empty = true;
            foreach ($cells as $value) {
                if ($value !== null && $value !== '') {
                    $empty = false;
                    break;
                }
            }
            if ($empty) {
                continue;
            }

            // Without a <dimension>, Spout fills the holes and sorts.
            if ($numColumns === 0 && !empty($cells)) {
                $max = max(array_keys($cells)) + 1;
                $sort = false;
                for ($i = 0; $i < $max; $i++) {
                    if (!isset($cells[$i])) {
                        $cells[$i] = '';
                        $sort = true;
                    }
                }
                if ($sort) {
                    ksort($cells);
                }
            }

            yield new FastXlsxRow($cells);
        }

        $xml->close();
    }

    /**
     * XMLReader positioned on a <c> element; leaves it on the matching
     * end element (or on the element itself when it is self-closing).
     */
    protected function readCell(XMLReader $xml)
    {
        $type = $xml->getAttribute('t') ?: 'n';
        $styleId = (int) $xml->getAttribute('s');
        $v = '';
        $inline = '';
        $hasV = false;

        if (!$xml->isEmptyElement) {
            $depth = $xml->depth;

            while ($xml->read()) {
                if ($xml->nodeType === XMLReader::END_ELEMENT && $xml->depth === $depth) {
                    break;
                }

                if ($xml->nodeType !== XMLReader::ELEMENT) {
                    continue;
                }

                if ($xml->localName === 'v' && !$hasV) {
                    $v = $xml->readString();
                    $hasV = true;
                } elseif ($xml->localName === 't') {
                    $inline .= self::unescape($xml->readString());
                }
            }
        }

        if ($v === '' && $type !== 'inlineStr') {
            return $v;
        }

        switch ($type) {
            case 'inlineStr':
                return $inline;

            case 's':
                return $this->sharedStrings[(int) $v] ?? '';

            case 'str':
                return self::unescape(trim($v));

            case 'b':
                return (bool) $v;

            case 'n':
                if ($this->isDateStyle($styleId)) {
                    $float = (float) $v;
                    if (!$this->isValidTimestamp($float)) {
                        return $float; // Spout returns the raw value of an invalid date
                    }

                    return $this->toDateTime($float);
                }

                $int = (int) $v;
                $float = (float) $v;

                return ((float) $int === $float) ? $int : $float;

            case 'd':
                try {
                    return new \DateTime($v);
                } catch (\Exception $e) {
                    return $v;
                }

            default:
                // Spout: InvalidValueException -> the raw <v> content.
                return $v;
        }
    }

    protected function isValidTimestamp(float $value): bool
    {
        return $this->use1904
            ? ($value >= -695055 && $value <= 2957003.9999884)
            : ($value >= -693593 && $value <= 2958465.9999884);
    }

    protected function toDateTime(float $value): \DateTime
    {
        $base = $this->use1904 ? '1904-01-01' : '1899-12-30';

        $days = (int) $value;
        $seconds = round(fmod($value, 1) * 86400, 0);

        $date = \DateTime::createFromFormat('|Y-m-d', $base);
        $date->modify('+' . $days . 'days');
        $date->modify('+' . $seconds . 'seconds');

        return $date;
    }

    protected function loadSharedStrings(): void
    {
        if ($this->sharedStrings !== null) {
            return;
        }

        $this->sharedStrings = [];

        if (!$this->sharedStringsPath) {
            return;
        }

        $xml = new XMLReader();
        if (!$xml->open('zip://' . $this->path . '#' . $this->sharedStringsPath, null, LIBXML_NONET | LIBXML_PARSEHUGE)) {
            throw new \RuntimeException('FastXlsxReader: cannot read shared strings');
        }

        $names = [];
        $current = null;

        while ($xml->read()) {
            if ($xml->nodeType === XMLReader::ELEMENT) {
                $names[$xml->depth] = $xml->localName;

                if ($xml->localName === 'si') {
                    if ($xml->isEmptyElement) {
                        $this->sharedStrings[] = '';
                    } else {
                        $current = '';
                    }
                } elseif ($xml->localName === 't' && $current !== null) {
                    $parent = $names[$xml->depth - 1] ?? null;

                    if ($parent === 'si' || $parent === 'r') {
                        $text = $xml->readString();
                        $preserve = $xml->getAttribute('xml:space') === 'preserve';
                        $current .= $preserve ? $text : trim($text);
                    }
                }
            } elseif ($xml->nodeType === XMLReader::END_ELEMENT && $xml->localName === 'si' && $current !== null) {
                $this->sharedStrings[] = self::unescape($current);
                $current = null;
            }
        }

        $xml->close();
    }

    protected function loadStyles(): void
    {
        if ($this->styles !== null) {
            return;
        }

        $this->styles = ['xfs' => [], 'formats' => [], 'cache' => []];

        if (!$this->stylesPath) {
            return;
        }

        $xml = new XMLReader();
        if (!$xml->open('zip://' . $this->path . '#' . $this->stylesPath, null, LIBXML_NONET | LIBXML_PARSEHUGE)) {
            return;
        }

        while ($xml->read()) {
            if ($xml->nodeType !== XMLReader::ELEMENT) {
                continue;
            }

            if ($xml->localName === 'numFmts') {
                while ($xml->read()) {
                    if ($xml->nodeType === XMLReader::ELEMENT && $xml->localName === 'numFmt') {
                        $this->styles['formats'][(int) $xml->getAttribute('numFmtId')] = $xml->getAttribute('formatCode');
                    } elseif ($xml->nodeType === XMLReader::END_ELEMENT && $xml->localName === 'numFmts') {
                        break;
                    }
                }
            } elseif ($xml->localName === 'cellXfs') {
                while ($xml->read()) {
                    if ($xml->nodeType === XMLReader::ELEMENT && $xml->localName === 'xf') {
                        $id = $xml->getAttribute('numFmtId');
                        $apply = $xml->getAttribute('applyNumberFormat');

                        $this->styles['xfs'][] = [
                            'numFmtId' => $id !== null ? (int) $id : null,
                            'apply' => $apply !== null ? (bool) $apply : null,
                        ];
                    } elseif ($xml->nodeType === XMLReader::END_ELEMENT && $xml->localName === 'cellXfs') {
                        break;
                    }
                }
            }
        }

        $xml->close();
    }

    protected function isDateStyle(int $styleId): bool
    {
        if ($styleId === 0 || !isset($this->styles['xfs'][$styleId])) {
            return false;
        }

        $xf = $this->styles['xfs'][$styleId];

        if ($xf['apply'] === false || $xf['numFmtId'] === null) {
            return false;
        }

        $id = $xf['numFmtId'];

        if (!isset($this->styles['cache'][$id])) {
            $code = $this->styles['formats'][$id] ?? null;

            $this->styles['cache'][$id] = in_array($id, self::BUILTIN_DATE_FORMATS)
                || self::isCustomDateFormat($code);
        }

        return $this->styles['cache'][$id];
    }

    protected static function isCustomDateFormat($code): bool
    {
        if ($code === null || strcasecmp($code, 'General') === 0) {
            return false;
        }

        $code = preg_replace('((?<!\\\)\[.+?(?<!\\\)\])', '', $code);

        foreach (['e', 'yy', 'm', 'd', 'h', 's'] as $char) {
            if (preg_match('/(?<!\\\)' . $char . '/i', $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Port of Spout's XLSX escaper ("_x000D_" style control characters).
     */
    protected static function unescape(string $string): string
    {
        if (strpos($string, '_x') === false) {
            return $string;
        }

        for ($code = 0x00; $code <= 0x1F; $code++) {
            $char = chr($code);

            if (!preg_match('/[\x00-\x08\x0B-\x0C\x0E-\x1F]/', $char)) {
                continue;
            }

            $escaped = '_x' . sprintf('%04s', strtoupper(dechex($code))) . '_';
            $string = preg_replace('/(?<!_x005F)(' . $escaped . ')/', $char, $string);
        }

        return preg_replace('/_x005F(_x[\dA-F]{4}_)/', '$1', $string);
    }

    protected static function columnIndex(string $cellRef): int
    {
        if (!preg_match('/^([A-Za-z]+)/', $cellRef, $m)) {
            throw new \RuntimeException('FastXlsxReader: invalid cell reference ' . $cellRef);
        }

        $letters = strtoupper($m[1]);
        $index = 0;

        for ($i = 0, $n = strlen($letters); $i < $n; $i++) {
            $index = $index * 26 + (ord($letters[$i]) - 64);
        }

        return $index - 1;
    }

    /**
     * Safety check: read the first $limit rows of every sheet with both
     * this reader and Spout and require identical values. Any difference
     * (unsupported workbook flavour, Spout upgrade...) returns false so the
     * caller falls back to Spout instead of importing wrong data.
     */
    public static function matchesSpout(string $path, int $limit = 80): bool
    {
        try {
            $fast = new self();
            $fast->open($path);

            $spout = \Box\Spout\Reader\Common\Creator\ReaderEntityFactory::createXLSXReader();
            $spout->open($path);

            $normalize = function (array $values) {
                return array_map(function ($v) {
                    return $v instanceof \DateTimeInterface ? 'dt:' . $v->format('c') : $v;
                }, array_values($values));
            };

            $fastSheets = iterator_to_array($fast->getSheetIterator(), false);
            $i = 0;

            foreach ($spout->getSheetIterator() as $sheet) {
                $fastSheet = $fastSheets[$i++] ?? null;

                if (!$fastSheet || $fastSheet->getName() !== $sheet->getName()) {
                    return self::mismatch('sheet names differ');
                }

                $fastRows = $fastSheet->getRowIterator();
                $fastRows->rewind();
                $count = 0;

                foreach ($sheet->getRowIterator() as $row) {
                    if ($count++ >= $limit) {
                        break;
                    }

                    if (!$fastRows->valid()) {
                        return self::mismatch('fast reader has fewer rows in sheet ' . $sheet->getName());
                    }

                    $expected = $normalize(array_map(fn ($c) => $c->getValue(), $row->getCells()));
                    $actual = $normalize($fastRows->current()->values);

                    if ($expected !== $actual) {
                        return self::mismatch('row ' . $count . ' differs in sheet ' . $sheet->getName());
                    }

                    $fastRows->next();
                }
            }

            $spout->close();
            $fast->close();

            return true;
        } catch (\Throwable $e) {
            return self::mismatch(get_class($e) . ': ' . $e->getMessage());
        }
    }

    protected static function mismatch(string $reason): bool
    {
        \Illuminate\Support\Facades\Log::warning('FastXlsxReader: falling back to Spout (' . $reason . ')');

        return false;
    }
}
