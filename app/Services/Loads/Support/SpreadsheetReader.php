<?php

namespace App\Services\Loads\Support;

use Generator;
use OpenSpout\Reader\Common\Creator\ReaderFactory;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SpreadsheetReader
{
    /**
     * @return Generator<int, array{row_number:int, values:array<int, mixed>}>
     */
    public function rows(string $path): Generator
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($extension === 'xls') {
            yield from $this->readWithPhpSpreadsheet($path);

            return;
        }

        yield from $this->readWithOpenSpout($path);
    }

    /**
     * @return Generator<int, array{row_number:int, values:array<int, mixed>}>
     */
    private function readWithOpenSpout(string $path): Generator
    {
        $reader = ReaderFactory::createFromFile($path);
        $reader->open($path);

        $rowNumber = 0;

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rowNumber++;

                yield [
                    'row_number' => $rowNumber,
                    'values' => $row->toArray(),
                ];
            }

            break;
        }

        $reader->close();
    }

    /**
     * @return Generator<int, array{row_number:int, values:array<int, mixed>}>
     */
    private function readWithPhpSpreadsheet(string $path): Generator
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);

        $spreadsheet = $reader->load($path);
        $worksheet = $spreadsheet->getSheet(0);

        foreach ($worksheet->toArray(null, true, true, false) as $index => $row) {
            yield [
                'row_number' => $index + 1,
                'values' => $row,
            ];
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
    }
}
