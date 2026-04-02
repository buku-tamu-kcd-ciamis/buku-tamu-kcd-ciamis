<?php

namespace App\Exports;

use App\Models\RoleUser;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserExport
{
    public function __construct(
        protected ?string $roleUserId = null,
    ) {
    }

    public function download(): StreamedResponse
    {
        $roleName = null;

        $query = User::query()
            ->with(['role_user', 'pegawai'])
            ->where(function ($userQuery): void {
                $userQuery->whereDoesntHave('role_user', function ($roleQuery): void {
                    $roleQuery->where('name', 'Super Admin');
                })->orWhereNull('role_user_id');
            })
            ->orderBy('name');

        if (filled($this->roleUserId)) {
            $query->where('role_user_id', $this->roleUserId);
            $roleName = RoleUser::query()->whereKey($this->roleUserId)->value('name');
        }

        $users = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data User');

        $headers = ['No', 'Nama', 'Email', 'Role', 'Nama Pegawai', 'NIP Pegawai', 'Jabatan'];
        $columns = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];

        foreach ($headers as $index => $header) {
            $sheet->setCellValue($columns[$index] . '1', $header);
        }

        $sheet->setCellValue('A2', 'Filter Role: ' . ($roleName ?? 'Semua Role'));
        $sheet->mergeCells('A2:G2');

        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '111827'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        $sheet->getStyle('A2:G2')->applyFromArray([
            'font' => [
                'italic' => true,
                'color' => ['rgb' => '4B5563'],
                'size' => 10,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $row = 3;
        foreach ($users as $index => $user) {
            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", (string) ($user->name ?? '-'));
            $sheet->setCellValue("C{$row}", (string) ($user->email ?? '-'));
            $sheet->setCellValue("D{$row}", (string) ($user->role_user?->name ?? '-'));
            $sheet->setCellValue("E{$row}", (string) ($user->pegawai?->nama ?? '-'));
            $sheet->setCellValueExplicit("F{$row}", (string) ($user->pegawai?->nip ?? '-'), DataType::TYPE_STRING);
            $sheet->setCellValue("G{$row}", (string) ($user->pegawai?->jabatan ?? '-'));
            $row++;
        }

        $lastDataRow = max(3, $row - 1);

        $sheet->getStyle("A1:G{$lastDataRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F:F')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(32);
        $sheet->getColumnDimension('D')->setWidth(24);
        $sheet->getColumnDimension('E')->setWidth(30);
        $sheet->getColumnDimension('F')->setWidth(22);
        $sheet->getColumnDimension('G')->setWidth(30);

        $sheet->freezePane('A3');

        $safeRoleToken = $roleName ? preg_replace('/[^a-z0-9]+/i', '-', strtolower($roleName)) : 'semua-role';
        $safeRoleToken = trim((string) $safeRoleToken, '-');
        $safeRoleToken = $safeRoleToken !== '' ? $safeRoleToken : 'semua-role';

        $fileName = 'data-user-' . $safeRoleToken . '-' . now()->format('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return new StreamedResponse(function () use ($writer): void {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
