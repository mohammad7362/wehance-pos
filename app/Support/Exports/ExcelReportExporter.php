<?php

namespace App\Support\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExcelReportExporter
{
    public function download(string $fileName, array $sheets): BinaryFileResponse
    {
        $spreadsheet = new Spreadsheet();

        foreach (array_values($sheets) as $index => $sheetData) {
            $worksheet = $index === 0
                ? $spreadsheet->getActiveSheet()
                : $spreadsheet->createSheet();

            $worksheet->setTitle($this->sanitizeWorksheetTitle($sheetData['title'] ?? 'Sheet ' . ($index + 1)));

            $rows = [];
            if (! empty($sheetData['headings'])) {
                $rows[] = $sheetData['headings'];
            }

            foreach ($sheetData['rows'] ?? [] as $row) {
                $rows[] = $row;
            }

            if (! empty($rows)) {
                $worksheet->fromArray($rows, null, 'A1', true);
                $this->autosizeColumns($worksheet, count($rows[0]));
            }
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'report_xlsx_');
        (new Xlsx($spreadsheet))->save($tempPath);
        $spreadsheet->disconnectWorksheets();

        return response()->download(
            $tempPath,
            $fileName,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    private function autosizeColumns($worksheet, int $columnCount): void
    {
        for ($column = 1; $column <= $columnCount; $column++) {
            $worksheet->getColumnDimensionByColumn($column)->setAutoSize(true);
        }
    }

    private function sanitizeWorksheetTitle(string $title): string
    {
        $title = preg_replace('/[\\\\\/?*\[\]:]/', '-', $title) ?? 'Sheet';

        return mb_substr($title, 0, 31);
    }
}