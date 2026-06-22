<?php

namespace App\Modules\Trace\Actions;

use App\Models\Tracer\Kuesioner;
use App\Modules\Trace\Services\KuesionerAnalyticsService;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportKuesionerAction
{
    public function __construct(
        protected KuesionerAnalyticsService $analyticsService,
    ) {}

    /**
     * Generate and return an XLSX export of kuesioner responses.
     */
    public function execute(
        Kuesioner $kuesioner,
        ?string $tahunLulus = null,
        ?string $prodi = null,
    ): BinaryFileResponse {
        $exportData = $this->analyticsService->buildExportData($kuesioner, $tahunLulus, $prodi);

        $columns  = $exportData['columns'];
        $dataRows = $exportData['dataRows'];

        // --- Generate XLSX with PhpSpreadsheet ---
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Responden');

        // Write header row
        foreach ($columns as $colIdx => $colName) {
            $cellCoord = Coordinate::stringFromColumnIndex($colIdx + 1) . '1';
            $sheet->setCellValue($cellCoord, $colName);
        }

        // Style header row
        $lastColLetter = Coordinate::stringFromColumnIndex(count($columns));
        $headerRange = 'A1:' . $lastColLetter . '1';

        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2563EB'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(35);

        // Write data rows
        foreach ($dataRows as $rowIdx => $rowData) {
            $excelRow = $rowIdx + 2;
            foreach ($rowData as $colIdx => $value) {
                $cellCoord = Coordinate::stringFromColumnIndex($colIdx + 1) . $excelRow;
                $sheet->setCellValue($cellCoord, $value);
            }

            // Zebra striping
            if ($rowIdx % 2 === 1) {
                $rowRange = 'A' . $excelRow . ':' . $lastColLetter . $excelRow;
                $sheet->getStyle($rowRange)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F8FAFC');
            }
        }

        // Style data area
        $lastDataRow = count($dataRows) + 1;
        if ($lastDataRow > 1) {
            $dataRange = 'A2:' . $lastColLetter . $lastDataRow;
            $sheet->getStyle($dataRange)->applyFromArray([
                'font' => ['size' => 10],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB'],
                    ],
                ],
            ]);

            // Center the "No" column
            $sheet->getStyle('A2:A' . $lastDataRow)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Auto-width columns
        foreach (range(0, count($columns) - 1) as $colIdx) {
            $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
            $maxLen = mb_strlen($columns[$colIdx]);
            foreach ($dataRows as $rowData) {
                $cellLen = mb_strlen((string)($rowData[$colIdx] ?? ''));
                if ($cellLen > $maxLen) $maxLen = $cellLen;
            }
            $width = min(max($maxLen + 3, 8), 40);
            $sheet->getColumnDimension($colLetter)->setWidth($width);
        }

        // Freeze header row & auto-filter
        $sheet->freezePane('A2');
        $sheet->setAutoFilter($headerRange);

        // --- Output as download ---
        $filename = 'Export_' . str_replace(' ', '_', $kuesioner->judul) . '_' . date('Ymd_His') . '.xlsx';

        $tempFile = tempnam(sys_get_temp_dir(), 'export_');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);
        $spreadsheet->disconnectWorksheets();

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
