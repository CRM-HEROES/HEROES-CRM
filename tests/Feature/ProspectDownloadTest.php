<?php

namespace Tests\Feature;

use App\Exports\ProspectDownload;
use App\Exports\ProspectExport;
use App\Models\Export;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Tests\TestCase;

class ProspectDownloadTest extends TestCase
{
    public function test_all_rows_are_read_in_bounded_batches_with_one_header(): void
    {
        $export = $this->fixture(620);
        $rows = iterator_to_array($export->rows());

        $this->assertCount(621, $rows);
        $this->assertSame(['Column 0', 'Column 1'], $rows[0]);
        $this->assertSame('00000', $rows[1][0]);
        $this->assertSame('00619', $rows[620][0]);
        $this->assertSame(620, $export->getCount());
        $this->assertSame([[250, 0], [250, 250], [250, 500]], $export->reads->batches);
    }

    public function test_limit_and_offset_are_preserved_across_batches(): void
    {
        $export = $this->fixture(620, 375, 50);
        $rows = iterator_to_array($export->rows());

        $this->assertCount(376, $rows);
        $this->assertSame('00050', $rows[1][0]);
        $this->assertSame('00424', $rows[375][0]);
        $this->assertSame(375, $export->getCount());
        $this->assertSame([[250, 50], [125, 300]], $export->reads->batches);
    }

    public function test_empty_exports_still_have_a_header(): void
    {
        $export = $this->fixture(0);

        $this->assertSame([['Column 0', 'Column 1']], iterator_to_array($export->rows()));
        $this->assertSame(0, $export->getCount());
    }

    public function test_large_xlsx_can_be_written_and_read_under_128_megabytes(): void
    {
        $export = $this->fixture(12000, 0, 0, 30);
        $response = (new ProspectDownload())->download($export, 'Prospects.xlsx');
        $path = $response->getFile()->getPathname();
        $reader = ReaderEntityFactory::createXLSXReader();

        try {
            $this->assertSame('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('Content-Type'));
            $this->assertStringContainsString('Prospects.xlsx', $response->headers->get('Content-Disposition'));
            $response->prepare(\Illuminate\Http\Request::create('/'));
            $this->assertGreaterThan(0, (int) $response->headers->get('Content-Length'));

            $reader->open($path);
            $count = 0;

            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $values = $row->toArray();

                    if ($count === 0) {
                        $this->assertSame('Column 0', $values[0]);
                    } elseif ($count === 1) {
                        $this->assertSame('00000', $values[0]);
                        $this->assertSame('Élodie & <Paris> + export', $values[1]);
                    }

                    if (++$count === 2) {
                        break 2;
                    }
                }
            }

            $xml = new \XMLReader();
            $xml->open('zip://' . $path . '#xl/worksheets/sheet1.xml');
            try {
                while ($xml->read() && !($xml->nodeType === \XMLReader::ELEMENT && $xml->localName === 'row')) {
                }

                $count = 0;
                do {
                    ++$count;
                    $lastRow = $xml->readOuterXml();
                } while ($xml->next('row'));
            } finally {
                $xml->close();
            }

            $this->assertSame(12001, $count);
            $this->assertStringContainsString('11999', $lastRow);
            $this->assertSame(12000, $export->getCount());
            $this->assertLessThan(128 * 1024 * 1024, memory_get_peak_usage(true));
        } finally {
            $reader->close();

            // The production response deletes this file after sending it.
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function test_dates_structured_values_and_scalar_types_are_preserved(): void
    {
        $export = $this->fixture(1, 0, 0, 6);
        $export->values = [
            'column_1' => \Illuminate\Support\Carbon::parse('2026-10-16 12:34:56'),
            'column_2' => new \DateTimeImmutable('2026-10-15 01:02:03'),
            'column_3' => ['city' => 'Paris'],
            'column_4' => true,
            'column_5' => 1.5,
        ];
        $response = (new ProspectDownload())->download($export, 'Prospects.xlsx');
        $path = $response->getFile()->getPathname();
        $reader = ReaderEntityFactory::createXLSXReader();

        try {
            $reader->open($path);
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $values = $row->toArray();
                }
            }

            $this->assertSame(['00000', '2026-10-16 12:34:56', '2026-10-15 01:02:03', '{"city":"Paris"}', true, 1.5], $values);
        } finally {
            $reader->close();
            unlink($path);
        }
    }

    public function test_temporary_file_is_deleted_after_the_download_is_sent(): void
    {
        $response = (new ProspectDownload())->download($this->fixture(1), 'Prospects.xlsx');
        $path = $response->getFile()->getPathname();

        ob_start();
        try {
            $response->sendContent();
        } finally {
            ob_end_clean();
        }

        $this->assertFileDoesNotExist($path);
    }

    protected function fixture(int $total, int $limit = 0, int $skip = 0, int $columns = 2): ProspectExport
    {
        return new class(new Export(), $limit, $skip, $total, $columns) extends ProspectExport {
            public object $reads;
            public array $values = [];
            protected int $total;
            protected int $columnCount;

            public function __construct(Export $export, int $limit, int $skip, int $total, int $columns)
            {
                $this->total = $total;
                $this->columnCount = $columns;
                $this->reads = (object) ['batches' => []];
                parent::__construct($export, $limit, $skip);
            }

            protected function getColumns()
            {
                return array_map(fn ($i) => [
                    'type' => self::MAPPING_FIELD_DEFAULT,
                    'field' => (object) ['name' => 'Column ' . $i, 'slug' => 'column_' . $i],
                ], range(0, $this->columnCount - 1));
            }

            protected function getProspects()
            {
                $this->reads->batches[] = [$this->limit, $this->skip];
                $prospects = [];

                for ($i = $this->skip; $i < min($this->total, $this->skip + $this->limit); ++$i) {
                    $prospect = (object) array_fill_keys(array_map(fn ($j) => 'column_' . $j, range(0, $this->columnCount - 1)), 'Élodie & <Paris> + export');
                    $prospect->column_0 = str_pad((string) $i, 5, '0', STR_PAD_LEFT);
                    foreach ($this->values as $key => $value) {
                        $prospect->{$key} = $value;
                    }
                    $prospects[] = $prospect;
                }

                return collect($prospects);
            }
        };
    }
}
