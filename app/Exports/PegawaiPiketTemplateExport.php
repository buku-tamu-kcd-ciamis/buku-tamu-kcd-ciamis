<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PegawaiPiketTemplateExport
{
    /**
     * Download the Excel template for importing pegawai piket data.
     */
    public function download(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Pegawai Piket');

        // ===== HEADER ROW =====
        $headers = ['No', 'Nama Lengkap', 'Email', 'Status'];
        $columns = ['A', 'B', 'C', 'D'];

        foreach ($headers as $i => $header) {
            $cell = $columns[$i] . '1';
            $sheet->setCellValue($cell, $header);
        }

        // Header styling
        $headerRange = 'A1:D1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2B5797'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(25);

        // ===== SAMPLE DATA =====
        $sampleData = [
            [1, 'Drs. H. Ahmad Suryadi, M.Pd.', 'piket.ahmad@cadisdik13.local', 'Aktif'],
            [2, 'Siti Nurhaliza, S.Pd.', '', 'Aktif'],
        ];

        foreach ($sampleData as $rowIndex => $rowData) {
            $row = $rowIndex + 2;
            foreach ($rowData as $colIndex => $value) {
                $cell = $columns[$colIndex] . $row;
                $sheet->setCellValue($cell, $value);
            }
        }

        // Sample data styling
        $sampleRange = 'A2:D3';
        $sheet->getStyle($sampleRange)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E8F0FE'],
            ],
            'font' => [
                'italic' => true,
                'color' => ['rgb' => '666666'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // ===== INSTRUCTIONS =====
        $sheet->setCellValue('A5', '📌 PETUNJUK PENGISIAN:');
        $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(11);
        $sheet->mergeCells('A5:D5');

        $instructions = [
            '1. Hapus baris contoh (baris 2-3) sebelum mengisi data baru.',
            '2. Kolom "Nama Lengkap" WAJIB diisi.',
            '3. Kolom "Email" opsional. Jika kosong, sistem membuat email dummy dari nama.',
            '4. Status: isi "Aktif" atau "Nonaktif" (default: Aktif jika dikosongkan).',
            '5. Jika nama sudah ada di database, data akan diperbarui (update).',
            '6. Simpan file dalam format .xlsx sebelum mengimpor.',
        ];

        foreach ($instructions as $i => $text) {
            $row = 6 + $i;
            $sheet->setCellValue("A{$row}", $text);
            $sheet->mergeCells("A{$row}:D{$row}");
            $sheet->getStyle("A{$row}")->getFont()->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF555555'));
        }

        // ===== COLUMN WIDTHS =====
        $sheet->getColumnDimension('A')->setWidth(6);   // No
        $sheet->getColumnDimension('B')->setWidth(40);   // Nama Lengkap
        $sheet->getColumnDimension('C')->setWidth(32);   // Email
        $sheet->getColumnDimension('D')->setWidth(12);   // Status

        // Center No and Status columns
        $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D:D')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Freeze header row
        $sheet->freezePane('A2');

        // ===== GENERATE RESPONSE =====
        $writer = new Xlsx($spreadsheet);

        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="template_import_pegawai_piket.xlsx"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
