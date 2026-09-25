<?php

namespace Tests\Feature;

use App\Support\Import\FastXlsxReader;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Tests\TestCase;

class FastXlsxReaderTest extends TestCase
{
    private function makeWorkbook(): string
    {
        $path = sys_get_temp_dir() . '/fast-xlsx-' . uniqid() . '.xlsx';

        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToFile($path);
        $writer->getCurrentSheet()->setName('Leads');

        $writer->addRow(WriterEntityFactory::createRowFromArray(['email', 'nom', 'age', 'score', 'actif', 'date']));
        // Excel serial 46218.4375 = 2026-07-15 10:30, displayed with a date format.
        $dateStyle = (new StyleBuilder())->setFormat('yyyy-mm-dd hh:mm')->build();
        $writer->addRow(WriterEntityFactory::createRow([
            WriterEntityFactory::createCell('a@x.fr'),
            WriterEntityFactory::createCell('  Dupont  '),
            WriterEntityFactory::createCell(42),
            WriterEntityFactory::createCell(3.5),
            WriterEntityFactory::createCell(true),
            WriterEntityFactory::createCell(46218.4375, $dateStyle),
        ]));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['b@x.fr', 'Élodie & <Cie>', 0, -1.25, false, '2026-07-16']));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['', '', '', '', '', ''])); // empty row: skipped
        $writer->addRow(WriterEntityFactory::createRowFromArray(['c@x.fr', 'ligne', 7, 1, false, null]));

        $writer->addNewSheetAndMakeItCurrent()->setName('Belgique');
        $writer->addRow(WriterEntityFactory::createRowFromArray(['email', 'tel']));
        $writer->addRow(WriterEntityFactory::createRowFromArray(['d@x.fr', '+32 470 12 34 56']));

        $writer->close();

        return $path;
    }

    public function test_it_reads_exactly_what_spout_reads(): void
    {
        $path = $this->makeWorkbook();

        try {
            $this->assertTrue(FastXlsxReader::matchesSpout($path, 1000));
        } finally {
            @unlink($path);
        }
    }

    public function test_it_lists_sheets_and_skips_empty_rows(): void
    {
        $path = $this->makeWorkbook();

        try {
            $reader = new FastXlsxReader();
            $reader->open($path);

            $sheets = [];
            foreach ($reader->getSheetIterator() as $sheet) {
                $rows = [];
                foreach ($sheet->getRowIterator() as $row) {
                    $rows[] = $row->values;
                }
                $sheets[$sheet->getName()] = $rows;
            }

            $this->assertSame(['Leads', 'Belgique'], array_keys($sheets));
            $this->assertCount(4, $sheets['Leads']); // header + 3 data rows, the blank one is skipped
            $this->assertSame('a@x.fr', $sheets['Leads'][1][0]);
            $this->assertSame(42, $sheets['Leads'][1][2]);
            $this->assertSame(3.5, $sheets['Leads'][1][3]);
            $this->assertInstanceOf(\DateTime::class, $sheets['Leads'][1][5]);
            $this->assertSame('2026-07-15 10:30', $sheets['Leads'][1][5]->format('Y-m-d H:i'));
            $this->assertSame('Élodie & <Cie>', $sheets['Leads'][2][1]);
            $this->assertSame([['email', 'tel'], ['d@x.fr', '+32 470 12 34 56']], $sheets['Belgique']);
        } finally {
            @unlink($path);
        }
    }
}
