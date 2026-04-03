<?php

namespace App\Filament\Resources\PegawaiIzinResource\Pages;

use App\Filament\Resources\PegawaiIzinResource;
use App\Models\PegawaiIzin;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListPegawaiIzin extends ListRecords
{
  protected static string $resource = PegawaiIzinResource::class;

  protected function getHeaderActions(): array
  {
    $dateRangeDefaults = $this->getExportDateRangeDefaults();

    return [
      Action::make('export_excel')
        ->label('Export Excel')
        ->icon('heroicon-o-arrow-down-tray')
        ->color('success')
        ->modalHeading('Export Data Izin Pegawai (Excel)')
        ->modalDescription('Filter data berdasarkan rentang tanggal, nama pegawai, jenis izin, status, dan nama piket. Anda dapat memilih lebih dari satu nilai.')
        ->schema([
          DatePicker::make('tanggal_mulai')
            ->label('Tanggal Mulai (Dari)')
            ->default($dateRangeDefaults['mulai'])
            ->native(false),
          DatePicker::make('tanggal_selesai')
            ->label('Tanggal Mulai (Sampai)')
            ->default($dateRangeDefaults['selesai'])
            ->native(false),
          Select::make('nama_pegawai')
            ->label('Nama Pegawai')
            ->options($this->getNamaPegawaiOptions())
            ->multiple()
            ->searchable()
            ->preload()
            ->native(false)
            ->placeholder('Semua Nama Pegawai')
            ->noOptionsMessage('Belum ada data nama pegawai.')
            ->noSearchResultsMessage('Nama pegawai tidak ditemukan.'),
          Select::make('jenis_izin')
            ->label('Jenis Izin')
            ->options(PegawaiIzin::JENIS_IZIN_LABELS)
            ->multiple()
            ->searchable()
            ->preload()
            ->native(false)
            ->placeholder('Semua Jenis Izin')
            ->noOptionsMessage('Belum ada data jenis izin.')
            ->noSearchResultsMessage('Jenis izin tidak ditemukan.'),
          Select::make('status')
            ->label('Status')
            ->options(PegawaiIzin::STATUS_LABELS)
            ->multiple()
            ->searchable()
            ->preload()
            ->native(false)
            ->placeholder('Semua Status')
            ->noOptionsMessage('Belum ada data status.')
            ->noSearchResultsMessage('Status tidak ditemukan.'),
          Select::make('nama_piket')
            ->label('Nama Piket')
            ->options($this->getNamaPiketOptions())
            ->multiple()
            ->searchable()
            ->preload()
            ->native(false)
            ->placeholder('Semua Nama Piket')
            ->noOptionsMessage('Belum ada data nama piket.')
            ->noSearchResultsMessage('Nama piket tidak ditemukan.'),
        ])
        ->action(fn(array $data) => $this->exportExcel($data)),
    ];
  }

  protected function exportExcel(array $filters = []): StreamedResponse
  {
    $tanggalMulai = !empty($filters['tanggal_mulai'])
      ? Carbon::parse($filters['tanggal_mulai'])->startOfDay()
      : null;
    $tanggalSelesai = !empty($filters['tanggal_selesai'])
      ? Carbon::parse($filters['tanggal_selesai'])->endOfDay()
      : null;

    $namaPegawaiDipilih = $this->normalizeMultiValue($filters['nama_pegawai'] ?? []);
    $jenisIzinDipilih = $this->normalizeMultiValue($filters['jenis_izin'] ?? []);
    $statusDipilih = $this->normalizeMultiValue($filters['status'] ?? []);
    $namaPiketDipilih = $this->normalizeMultiValue($filters['nama_piket'] ?? []);

    if ($tanggalMulai && $tanggalSelesai && $tanggalMulai->gt($tanggalSelesai)) {
      [$tanggalMulai, $tanggalSelesai] = [$tanggalSelesai->copy()->startOfDay(), $tanggalMulai->copy()->endOfDay()];
    }

    $query = PegawaiIzin::query()->latest('created_at');

    if ($tanggalMulai) {
      $query->whereDate('tanggal_mulai', '>=', $tanggalMulai->toDateString());
    }

    if ($tanggalSelesai) {
      $query->whereDate('tanggal_mulai', '<=', $tanggalSelesai->toDateString());
    }

    if ($namaPegawaiDipilih !== []) {
      $query->whereIn('nama_pegawai', $namaPegawaiDipilih);
    }

    if ($jenisIzinDipilih !== []) {
      $query->whereIn('jenis_izin', $jenisIzinDipilih);
    }

    if ($statusDipilih !== []) {
      $query->whereIn('status', $statusDipilih);
    }

    if ($namaPiketDipilih !== []) {
      $query->whereIn('nama_piket', $namaPiketDipilih);
    }

    $rows = $query->get([
      'nama_pegawai',
      'nip',
      'jabatan',
      'unit_kerja',
      'nomor_hp',
      'jenis_izin',
      'tanggal_mulai',
      'tanggal_selesai',
      'status',
      'nama_piket',
      'keterangan',
      'diverifikasi_oleh',
      'diverifikasi_pada',
      'created_at',
    ]);

    $spreadsheet = new Spreadsheet();
    $usedTitles = [];

    $mainSheet = $spreadsheet->getActiveSheet();
    $mainSheet->setTitle('Semua Data');
    $usedTitles[] = 'Semua Data';

    $this->fillExportSheet(
      $mainSheet,
      $rows,
      'Rekap Izin Pegawai',
      [
        'Tanggal Mulai Dari' => $tanggalMulai?->format('d/m/Y') ?? 'Semua',
        'Tanggal Mulai Sampai' => $tanggalSelesai?->format('d/m/Y') ?? 'Semua',
        'Nama Pegawai' => $namaPegawaiDipilih !== [] ? implode(', ', $namaPegawaiDipilih) : 'Semua',
        'Jenis Izin' => $this->implodeMappedLabels($jenisIzinDipilih, PegawaiIzin::JENIS_IZIN_LABELS),
        'Status' => $this->implodeMappedLabels($statusDipilih, PegawaiIzin::STATUS_LABELS),
        'Nama Piket' => $namaPiketDipilih !== [] ? implode(', ', $namaPiketDipilih) : 'Semua',
      ]
    );

    foreach ($namaPegawaiDipilih as $namaPegawai) {
      $sheet = $spreadsheet->createSheet();
      $sheetTitle = $this->makeUniqueSheetTitle('Pegawai - ' . $namaPegawai, $usedTitles);
      $sheet->setTitle($sheetTitle);

      $this->fillExportSheet(
        $sheet,
        $rows->where('nama_pegawai', $namaPegawai)->values(),
        'Data Nama Pegawai: ' . $namaPegawai
      );
    }

    foreach ($jenisIzinDipilih as $jenisIzin) {
      $sheet = $spreadsheet->createSheet();
      $jenisLabel = PegawaiIzin::JENIS_IZIN_LABELS[$jenisIzin] ?? $jenisIzin;
      $sheetTitle = $this->makeUniqueSheetTitle('Jenis - ' . $jenisLabel, $usedTitles);
      $sheet->setTitle($sheetTitle);

      $this->fillExportSheet(
        $sheet,
        $rows->where('jenis_izin', $jenisIzin)->values(),
        'Data Jenis Izin: ' . $jenisLabel
      );
    }

    foreach ($statusDipilih as $status) {
      $sheet = $spreadsheet->createSheet();
      $statusLabel = PegawaiIzin::STATUS_LABELS[$status] ?? ucfirst($status);
      $sheetTitle = $this->makeUniqueSheetTitle('Status - ' . $statusLabel, $usedTitles);
      $sheet->setTitle($sheetTitle);

      $this->fillExportSheet(
        $sheet,
        $rows->where('status', $status)->values(),
        'Data Status: ' . $statusLabel
      );
    }

    foreach ($namaPiketDipilih as $namaPiket) {
      $sheet = $spreadsheet->createSheet();
      $sheetTitle = $this->makeUniqueSheetTitle('Piket - ' . $namaPiket, $usedTitles);
      $sheet->setTitle($sheetTitle);

      $this->fillExportSheet(
        $sheet,
        $rows->where('nama_piket', $namaPiket)->values(),
        'Data Nama Piket: ' . $namaPiket
      );
    }

    $dateToken = 'semua';

    if ($tanggalMulai && $tanggalSelesai) {
      $dateToken = $tanggalMulai->format('Ymd') . '-sampai-' . $tanggalSelesai->format('Ymd');
    } elseif ($tanggalMulai) {
      $dateToken = 'mulai-' . $tanggalMulai->format('Ymd');
    } elseif ($tanggalSelesai) {
      $dateToken = 'sampai-' . $tanggalSelesai->format('Ymd');
    }

    $fileName = 'izin-pegawai-' . $dateToken . '-' . now()->format('His') . '.xlsx';
    $writer = new Xlsx($spreadsheet);

    return response()->streamDownload(function () use ($writer): void {
      $writer->save('php://output');
    }, $fileName, [
      'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'Cache-Control' => 'max-age=0',
    ]);
  }

  protected function normalizeMultiValue(mixed $value): array
  {
    if (is_string($value)) {
      $value = [$value];
    }

    if (!is_array($value)) {
      return [];
    }

    $normalized = [];

    foreach ($value as $item) {
      $item = trim((string) $item);

      if ($item === '') {
        continue;
      }

      $normalized[$item] = $item;
    }

    return array_values($normalized);
  }

  protected function implodeMappedLabels(array $values, array $labels): string
  {
    if ($values === []) {
      return 'Semua';
    }

    $mapped = array_map(function (string $value) use ($labels): string {
      return (string) ($labels[$value] ?? $value);
    }, $values);

    return implode(', ', $mapped);
  }

  protected function fillExportSheet(Worksheet $sheet, $rows, string $title, array $filters = []): void
  {
    $headers = [
      'No',
      'Nama Pegawai',
      'NIP',
      'Jabatan',
      'Unit Kerja',
      'Nomor HP',
      'Jenis Izin',
      'Tanggal Mulai',
      'Tanggal Selesai',
      'Status',
      'Nama Piket',
      'Keterangan',
      'Verifikator KCD',
      'Waktu Verifikasi',
      'Dibuat Pada',
    ];

    $sheet->setCellValue('A1', $title);
    $sheet->mergeCells('A1:O1');

    $summaryParts = [];
    foreach ($filters as $label => $value) {
      $summaryParts[] = $label . ' = ' . $value;
    }

    $sheet->setCellValue('A2', 'Filter: ' . ($summaryParts !== [] ? implode(' | ', $summaryParts) : 'Semua data'));
    $sheet->mergeCells('A2:O2');

    $sheet->fromArray($headers, null, 'A4');

    $sheet->getStyle('A1:O1')->applyFromArray([
      'font' => [
        'bold' => true,
        'size' => 13,
        'color' => ['rgb' => '111827'],
      ],
      'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_LEFT,
        'vertical' => Alignment::VERTICAL_CENTER,
      ],
    ]);

    $sheet->getStyle('A2:O2')->applyFromArray([
      'font' => [
        'italic' => true,
        'size' => 10,
        'color' => ['rgb' => '4B5563'],
      ],
      'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_LEFT,
        'vertical' => Alignment::VERTICAL_CENTER,
      ],
    ]);

    $sheet->getStyle('A4:O4')->applyFromArray([
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

    $rowIndex = 5;

    foreach ($rows as $index => $row) {
      $sheet->setCellValue("A{$rowIndex}", $index + 1);
      $sheet->setCellValue("B{$rowIndex}", (string) ($row->nama_pegawai ?? '-'));
      $sheet->setCellValueExplicit("C{$rowIndex}", (string) ($row->nip ?? '-'), DataType::TYPE_STRING);
      $sheet->setCellValue("D{$rowIndex}", (string) ($row->jabatan ?? '-'));
      $sheet->setCellValue("E{$rowIndex}", (string) ($row->unit_kerja ?? '-'));
      $sheet->setCellValue("F{$rowIndex}", $this->formatNomorHp($row->nomor_hp));
      $sheet->setCellValue("G{$rowIndex}", (string) (PegawaiIzin::JENIS_IZIN_LABELS[$row->jenis_izin] ?? $row->jenis_izin ?? '-'));
      $sheet->setCellValue("H{$rowIndex}", $row->tanggal_mulai ? Carbon::parse($row->tanggal_mulai)->format('d/m/Y') : '-');
      $sheet->setCellValue("I{$rowIndex}", $row->tanggal_selesai ? Carbon::parse($row->tanggal_selesai)->format('d/m/Y') : '-');
      $sheet->setCellValue("J{$rowIndex}", (string) (PegawaiIzin::STATUS_LABELS[$row->status] ?? $row->status ?? '-'));
      $sheet->setCellValue("K{$rowIndex}", (string) ($row->nama_piket ?? '-'));
      $sheet->setCellValue("L{$rowIndex}", (string) ($row->keterangan ?? '-'));
      $sheet->setCellValue("M{$rowIndex}", (string) ($row->diverifikasi_oleh ?? '-'));
      $sheet->setCellValue("N{$rowIndex}", $row->diverifikasi_pada ? Carbon::parse($row->diverifikasi_pada)->format('d/m/Y H:i') : '-');
      $sheet->setCellValue("O{$rowIndex}", $row->created_at ? Carbon::parse($row->created_at)->format('d/m/Y H:i') : '-');

      $rowIndex++;
    }

    $lastDataRow = max(5, $rowIndex - 1);

    $sheet->getStyle("A4:O{$lastDataRow}")->applyFromArray([
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
    $sheet->getStyle('C:C')->getNumberFormat()->setFormatCode('@');

    $sheet->getColumnDimension('A')->setWidth(6);
    $sheet->getColumnDimension('B')->setWidth(28);
    $sheet->getColumnDimension('C')->setWidth(22);
    $sheet->getColumnDimension('D')->setWidth(26);
    $sheet->getColumnDimension('E')->setWidth(26);
    $sheet->getColumnDimension('F')->setWidth(18);
    $sheet->getColumnDimension('G')->setWidth(18);
    $sheet->getColumnDimension('H')->setWidth(16);
    $sheet->getColumnDimension('I')->setWidth(16);
    $sheet->getColumnDimension('J')->setWidth(16);
    $sheet->getColumnDimension('K')->setWidth(22);
    $sheet->getColumnDimension('L')->setWidth(30);
    $sheet->getColumnDimension('M')->setWidth(24);
    $sheet->getColumnDimension('N')->setWidth(20);
    $sheet->getColumnDimension('O')->setWidth(20);

    $sheet->freezePane('A5');
  }

  protected function makeUniqueSheetTitle(string $baseTitle, array &$usedTitles): string
  {
    $title = trim((string) preg_replace('/[\\\\\/?*\[\]:]/', '-', $baseTitle));

    if ($title === '') {
      $title = 'Sheet';
    }

    $title = mb_substr($title, 0, 31);

    if (!in_array($title, $usedTitles, true)) {
      $usedTitles[] = $title;

      return $title;
    }

    $counter = 2;

    do {
      $suffix = ' (' . $counter . ')';
      $candidate = mb_substr($title, 0, 31 - mb_strlen($suffix)) . $suffix;
      $counter++;
    } while (in_array($candidate, $usedTitles, true));

    $usedTitles[] = $candidate;

    return $candidate;
  }

  protected function getExportDateRangeDefaults(): array
  {
    $firstDate = PegawaiIzin::query()->orderBy('tanggal_mulai')->value('tanggal_mulai');
    $lastDate = PegawaiIzin::query()->orderByDesc('tanggal_mulai')->value('tanggal_mulai');

    return [
      'mulai' => $firstDate ? Carbon::parse($firstDate)->toDateString() : null,
      'selesai' => $lastDate ? Carbon::parse($lastDate)->toDateString() : now()->toDateString(),
    ];
  }

  protected function getNamaPegawaiOptions(): array
  {
    return PegawaiIzin::query()
      ->whereNotNull('nama_pegawai')
      ->where('nama_pegawai', '!=', '')
      ->distinct()
      ->orderBy('nama_pegawai')
      ->pluck('nama_pegawai', 'nama_pegawai')
      ->toArray();
  }

  protected function getNamaPiketOptions(): array
  {
    return PegawaiIzin::query()
      ->whereNotNull('nama_piket')
      ->where('nama_piket', '!=', '')
      ->distinct()
      ->orderBy('nama_piket')
      ->pluck('nama_piket', 'nama_piket')
      ->toArray();
  }

  protected function formatNomorHp(?string $nomorHp): string
  {
    if (!$nomorHp) {
      return '-';
    }

    $cleaned = preg_replace('/[^0-9]/', '', $nomorHp);

    if ($cleaned === '') {
      return '-';
    }

    if (str_starts_with($cleaned, '62')) {
      $cleaned = substr($cleaned, 2);
    } elseif (str_starts_with($cleaned, '0')) {
      $cleaned = substr($cleaned, 1);
    }

    return '+62' . $cleaned;
  }
}
