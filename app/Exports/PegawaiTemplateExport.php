<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PegawaiTemplateExport
{
    /**
     * Download the Excel template for importing pegawai data.
     */
    public function download(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Pegawai');

        // ===== HEADER ROW =====
        $headers = ['No', 'Nama', 'NIP', 'Jabatan', 'Unit Kerja', 'Nomor HP', 'Status'];
        $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];

        foreach ($headers as $i => $header) {
            $cell = $columns[$i] . '1';
            $sheet->setCellValue($cell, $header);
        }

        // Header styling
        $headerRange = 'A1:G1';
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

        // ===== SAMPLE DATA (2 rows) =====
        $sampleData = [
            [1, 'Drs. H. Ahmad Suryadi, M.Pd.', '198501012010011001', 'Kepala Cabang Dinas', 'Cadisdik Wilayah XIII', '812-3456-7890', 'Aktif'],
            [2, 'Siti Nurhaliza, S.Pd.', '199003152015012002', 'Staff Tata Usaha', 'Sub Bagian Tata Usaha', '857-1234-5678', 'Aktif'],
        ];

        foreach ($sampleData as $rowIndex => $rowData) {
            $row = $rowIndex + 2;
            foreach ($rowData as $colIndex => $value) {
                $cell = $columns[$colIndex] . $row;
                $sheet->setCellValue($cell, $value);

                // Set NIP as text to preserve leading zeros
                if ($colIndex === 2) {
                    $sheet->getStyle($cell)->getNumberFormat()
                        ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
                    $sheet->setCellValueExplicit($cell, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                }
            }
        }

        // Sample data styling (light blue background)
        $sampleRange = 'A2:G3';
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

        // ===== INSTRUCTIONS ROW =====
        $sheet->setCellValue('A5', '📌 PETUNJUK PENGISIAN:');
        $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(11);
        $sheet->mergeCells('A5:G5');

        $instructions = [
            '1. Hapus baris contoh (baris 2-3) sebelum mengisi data baru.',
            '2. Kolom "Nama" dan "NIP" WAJIB diisi.',
            '3. NIP harus tepat 18 digit angka.',
            '4. Nomor HP: format 8xx-xxxx-xxxx (tanpa kode +62 atau 0).',
            '5. Status: isi "Aktif" atau "Nonaktif" (default: Aktif jika dikosongkan).',
            '6. Jika NIP sudah ada di database, data akan diperbarui (update).',
            '7. Simpan file dalam format .xlsx sebelum mengimpor.',
        ];

        foreach ($instructions as $i => $text) {
            $row = 6 + $i;
            $sheet->setCellValue("A{$row}", $text);
            $sheet->mergeCells("A{$row}:G{$row}");
            $sheet->getStyle("A{$row}")->getFont()->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF555555'));
        }

        // ===== COLUMN WIDTHS =====
        $sheet->getColumnDimension('A')->setWidth(6);   // No
        $sheet->getColumnDimension('B')->setWidth(35);  // Nama
        $sheet->getColumnDimension('C')->setWidth(22);  // NIP
        $sheet->getColumnDimension('D')->setWidth(28);  // Jabatan
        $sheet->getColumnDimension('E')->setWidth(30);  // Unit Kerja
        $sheet->getColumnDimension('F')->setWidth(18);  // Nomor HP
        $sheet->getColumnDimension('G')->setWidth(12);  // Status

        // NIP column as text format
        $sheet->getStyle('C:C')->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

        // Center No and Status columns
        $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G:G')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Freeze header row
        $sheet->freezePane('A2');

        // ===== GENERATE RESPONSE =====
        $writer = new Xlsx($spreadsheet);

        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="template_import_pegawai.xlsx"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
