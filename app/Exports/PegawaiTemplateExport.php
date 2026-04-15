<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
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

        $roleOptions = ['Staff', 'Piket', 'Kepala Cabang Dinas'];
        $roleFormula = '"' . implode(',', $roleOptions) . '"';

        // ===== HEADER ROW =====
        $headers = ['No', 'Nama Pegawai', 'NIP', 'Pangkat/Golongan', 'Jabatan', 'Unit Kerja', 'Role User (Opsional)'];
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
            [1, 'DWI YANTI ESTRININGRUM, S.Sos., M.Pd.', '197202022005012011', 'Pembina TK.I, IV/b', 'KEPALA CABANG PENDIDIKAN WILAYAH XIII', 'CABANG PENDIDIKAN WILAYAH XIII', 'Kepala Cabang Dinas'],
            [2, 'RUDIANTO, M.Pd.', '197105111999031002', 'Pembina TK.I, IV/b', 'KEPALA SUBBAGIAN TATA USAHA', 'CABANG PENDIDIKAN WILAYAH XIII', 'Staff'],
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

        for ($row = 2; $row <= 500; $row++) {
            $validation = $sheet->getCell("G{$row}")->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(true);
            $validation->setShowDropDown(true);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setErrorTitle('Role tidak valid');
            $validation->setError('Pilih role sesuai daftar pada dropdown.');
            $validation->setPromptTitle('Pilih Role User');
            $validation->setPrompt('Pilih Staff, Piket, atau Kepala Cabang Dinas.');
            $validation->setFormula1($roleFormula);
        }

        // ===== INSTRUCTIONS ROW =====
        $sheet->setCellValue('A5', '📌 PETUNJUK PENGISIAN:');
        $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(11);
        $sheet->mergeCells('A5:G5');

        $instructions = [
            '1. Hapus baris contoh (baris 2-3) sebelum mengisi data baru.',
            '2. Format utama import: Nama Pegawai, NIP, Pangkat/Golongan, Jabatan, Unit Kerja.',
            '3. Kolom "Pangkat/Golongan" dibaca untuk kompatibilitas format, namun tidak disimpan ke database.',
            '4. Kolom "Role User (Opsional)": isi Staff, Piket, atau Kepala Cabang Dinas bila ingin sinkron role user.',
            '5. Kolom "Nama Pegawai" wajib diisi. Kolom "NIP" opsional.',
            '6. Jika kolom NIP diisi, nilainya harus tepat 18 digit angka.',
            '7. Jika NIP sudah ada di database, data akan diperbarui (update).',
            '8. Simpan file dalam format .xlsx sebelum mengimpor.',
        ];

        foreach ($instructions as $i => $text) {
            $row = 6 + $i;
            $sheet->setCellValue("A{$row}", $text);
            $sheet->mergeCells("A{$row}:G{$row}");
            $sheet->getStyle("A{$row}")->getFont()->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF555555'));
        }

        // ===== COLUMN WIDTHS =====
        $sheet->getColumnDimension('A')->setWidth(6);   // No
        $sheet->getColumnDimension('B')->setWidth(42);  // Nama Pegawai
        $sheet->getColumnDimension('C')->setWidth(22);  // NIP
        $sheet->getColumnDimension('D')->setWidth(24);  // Pangkat/Golongan
        $sheet->getColumnDimension('E')->setWidth(44);  // Jabatan
        $sheet->getColumnDimension('F')->setWidth(36);  // Unit Kerja
        $sheet->getColumnDimension('G')->setWidth(24);  // Role User (Opsional)

        // NIP column as text format
        $sheet->getStyle('C:C')->getNumberFormat()
            ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

        // Center No and Role columns
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
