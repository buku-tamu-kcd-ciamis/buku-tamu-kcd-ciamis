<?php

namespace App\Filament\Resources\RekapIzinPegawaiResource\Pages;

use App\Filament\Resources\RekapIzinPegawaiResource;
use App\Models\PegawaiIzin;
use Filament\Resources\Pages\Page;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Schemas\Schema;
use Filament\Infolists;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class ViewRekapIzinPegawai extends Page implements HasInfolists
{
  use InteractsWithInfolists, WithPagination;

  protected static string $resource = RekapIzinPegawaiResource::class;

  protected string $view = 'filament.resources.rekap-izin-pegawai.view';

  public string $nip = '';
  public $rekap = null;
  public $allRiwayat = null;
  public int $riwayatPerPage = 3;

  public function mount(string $record): void
  {
    $this->nip = $record;

    $perPage = (int) request()->query('per_page', 3);
    $this->riwayatPerPage = in_array($perPage, [3, 5, 10], true) ? $perPage : 3;

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
        DB::raw("SUM(CASE WHEN status = 'aktif' AND tanggal_selesai >= CURDATE() THEN 1 ELSE 0 END) as sedang_izin"),
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
      ->paginate($this->riwayatPerPage);
  }

  public function rekapInfolist(Schema $schema): Schema
  {
    return $schema
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
      ->components([
        Infolists\Components\TextEntry::make('nama_pegawai'),
        Infolists\Components\TextEntry::make('nip'),
        Infolists\Components\TextEntry::make('jabatan'),
        Infolists\Components\TextEntry::make('unit_kerja'),
        Infolists\Components\TextEntry::make('total_izin'),
        Infolists\Components\TextEntry::make('total_hari'),
        Infolists\Components\TextEntry::make('terakhir_izin'),
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
    return [];
  }
}
