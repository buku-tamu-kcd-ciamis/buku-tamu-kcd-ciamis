<?php

namespace App\Filament\Resources\RekapIzinPegawaiResource\Pages;

use App\Filament\Resources\RekapIzinPegawaiResource;
use App\Models\PegawaiIzin;
use Filament\Resources\Pages\Page;
use Filament\Actions;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Infolists;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class ViewRekapIzinPegawai extends Page implements HasInfolists
{
  use InteractsWithInfolists, WithPagination;

  protected static string $resource = RekapIzinPegawaiResource::class;

  protected static string $view = 'filament.resources.rekap-izin-pegawai.view';

  public string $nip = '';
  public $rekap = null;
  public $allRiwayat = null;

  public function mount(string $record): void
  {
    $this->nip = $record;

    // Ambil data rekap aggregate
    $this->rekap = PegawaiIzin::query()
      ->select(
        'nama_pegawai',
        'nip',
        'jabatan',
        'unit_kerja',
        'nomor_hp',
        DB::raw('COUNT(*) as total_izin'),
        DB::raw("SUM(CASE WHEN jenis_izin = 'sakit' THEN 1 ELSE 0 END) as total_sakit"),
        DB::raw("SUM(CASE WHEN jenis_izin = 'cuti' THEN 1 ELSE 0 END) as total_cuti"),
        DB::raw("SUM(CASE WHEN jenis_izin = 'dinas_luar' THEN 1 ELSE 0 END) as total_dinas_luar"),
        DB::raw("SUM(CASE WHEN jenis_izin = 'izin_pribadi' THEN 1 ELSE 0 END) as total_izin_pribadi"),
        DB::raw("SUM(CASE WHEN jenis_izin = 'lainnya' THEN 1 ELSE 0 END) as total_lainnya"),
        DB::raw("SUM(DATEDIFF(tanggal_selesai, tanggal_mulai) + 1) as total_hari"),
        DB::raw("MAX(tanggal_mulai) as terakhir_izin"),
        DB::raw("SUM(CASE WHEN status = 'aktif' THEN 1 ELSE 0 END) as sedang_izin"),
      )
      ->where('nip', $this->nip)
      ->groupBy('nip', 'nama_pegawai', 'jabatan', 'unit_kerja', 'nomor_hp')
      ->first();

    if (!$this->rekap) {
      abort(404);
    }

    // Ambil semua riwayat izin untuk stats
    $this->allRiwayat = PegawaiIzin::where('nip', $this->nip)
      ->orderBy('tanggal_mulai', 'desc')
      ->get();
  }

  public function getRiwayatPaginated()
  {
    return PegawaiIzin::where('nip', $this->nip)
      ->orderBy('tanggal_mulai', 'desc')
      ->paginate(5);
  }

  public function rekapInfolist(Infolist $infolist): Infolist
  {
    return $infolist
      ->state([
        'nama_pegawai' => $this->rekap->nama_pegawai,
        'nip' => $this->rekap->nip,
        'jabatan' => $this->rekap->jabatan,
        'unit_kerja' => $this->rekap->unit_kerja,
        'nomor_hp' => $this->rekap->nomor_hp,
        'total_izin' => (int) $this->rekap->total_izin,
        'total_hari' => (int) $this->rekap->total_hari,
        'total_sakit' => (int) $this->rekap->total_sakit,
        'total_cuti' => (int) $this->rekap->total_cuti,
        'total_dinas_luar' => (int) $this->rekap->total_dinas_luar,
        'total_izin_pribadi' => (int) $this->rekap->total_izin_pribadi,
        'total_lainnya' => (int) $this->rekap->total_lainnya,
        'terakhir_izin' => $this->rekap->terakhir_izin,
        'sedang_izin' => (int) $this->rekap->sedang_izin,
      ])
      ->schema([
        // === Informasi Pegawai ===
        Infolists\Components\Section::make('Informasi Pegawai')
          ->icon('heroicon-o-user')
          ->columns(2)
          ->schema([
            Infolists\Components\TextEntry::make('nama_pegawai')
              ->label('Nama Pegawai')
              ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
              ->weight('bold')
              ->icon('heroicon-o-user')
              ->columnSpanFull(),
            Infolists\Components\TextEntry::make('nip')
              ->label('NIP')
              ->icon('heroicon-o-identification')
              ->copyable(),
            Infolists\Components\TextEntry::make('nomor_hp')
              ->label('No. HP')
              ->icon('heroicon-o-phone')
              ->formatStateUsing(function ($state) {
                if (!$state) return '-';
                $cleaned = preg_replace('/[^0-9]/', '', $state);
                if (str_starts_with($cleaned, '0')) {
                  $cleaned = substr($cleaned, 1);
                }
                return '+62' . $cleaned;
              })
              ->copyable()
              ->placeholder('-'),
            Infolists\Components\TextEntry::make('jabatan')
              ->label('Jabatan')
              ->icon('heroicon-o-briefcase')
              ->placeholder('-'),
            Infolists\Components\TextEntry::make('unit_kerja')
              ->label('Unit Kerja')
              ->icon('heroicon-o-building-office')
              ->placeholder('-'),
          ]),

        // === Rekap Statistik ===
        Infolists\Components\Section::make('Rekap Statistik')
          ->icon('heroicon-o-chart-bar')
          ->columns(4)
          ->schema([
            Infolists\Components\TextEntry::make('total_izin')
              ->label('Total Izin')
              ->icon('heroicon-o-document-duplicate')
              ->badge()
              ->suffix(' kali')
              ->color(fn($state): string => match (true) {
                $state >= 5 => 'danger',
                $state >= 3 => 'warning',
                default => 'success',
              }),
            Infolists\Components\TextEntry::make('total_hari')
              ->label('Total Hari')
              ->icon('heroicon-o-calendar-days')
              ->suffix(' hari')
              ->color(fn($state): string => match (true) {
                $state >= 10 => 'danger',
                $state >= 5 => 'warning',
                default => 'gray',
              }),
            Infolists\Components\TextEntry::make('terakhir_izin')
              ->label('Terakhir Izin')
              ->icon('heroicon-o-clock')
              ->date('d F Y')
              ->placeholder('-'),
            Infolists\Components\TextEntry::make('sedang_izin')
              ->label('Status Saat Ini')
              ->icon(fn($state) => $state > 0 ? 'heroicon-o-clock' : 'heroicon-o-check-circle')
              ->badge()
              ->formatStateUsing(fn($state) => $state > 0 ? 'Sedang Izin' : 'Aktif')
              ->color(fn($state) => $state > 0 ? 'warning' : 'success'),
          ]),

        // === Breakdown Per Jenis Izin ===
        Infolists\Components\Section::make('Breakdown Per Jenis Izin')
          ->icon('heroicon-o-chart-bar-square')
          ->columns(5)
          ->schema([
            Infolists\Components\TextEntry::make('total_sakit')
              ->label('Sakit')
              ->icon('heroicon-o-heart')
              ->badge()
              ->suffix(' kali')
              ->color(fn($state) => $state > 0 ? 'danger' : 'gray'),
            Infolists\Components\TextEntry::make('total_cuti')
              ->label('Cuti')
              ->icon('heroicon-o-sun')
              ->badge()
              ->suffix(' kali')
              ->color(fn($state) => $state > 0 ? 'info' : 'gray'),
            Infolists\Components\TextEntry::make('total_dinas_luar')
              ->label('Dinas Luar')
              ->icon('heroicon-o-map-pin')
              ->badge()
              ->suffix(' kali')
              ->color(fn($state) => $state > 0 ? 'warning' : 'gray'),
            Infolists\Components\TextEntry::make('total_izin_pribadi')
              ->label('Izin Pribadi')
              ->icon('heroicon-o-user')
              ->badge()
              ->suffix(' kali')
              ->color(fn($state) => $state > 0 ? 'primary' : 'gray'),
            Infolists\Components\TextEntry::make('total_lainnya')
              ->label('Lainnya')
              ->icon('heroicon-o-document')
              ->badge()
              ->suffix(' kali')
              ->color('gray'),
          ]),
      ]);
  }

  public function getTitle(): string
  {
    return 'Detail Rekap — ' . ($this->rekap->nama_pegawai ?? '');
  }

  public function getBreadcrumbs(): array
  {
    return [
      RekapIzinPegawaiResource::getUrl() => 'Rekap Izin Pegawai',
      '#' => $this->rekap->nama_pegawai ?? 'Detail',
    ];
  }

  protected function getHeaderActions(): array
  {
    return [
      Actions\Action::make('back')
        ->label('Kembali')
        ->icon('heroicon-o-arrow-left')
        ->color('gray')
        ->url(RekapIzinPegawaiResource::getUrl()),
    ];
  }
}
