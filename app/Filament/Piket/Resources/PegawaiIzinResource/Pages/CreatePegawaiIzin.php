<?php

namespace App\Filament\Piket\Resources\PegawaiIzinResource\Pages;

use App\Filament\Piket\Resources\PegawaiIzinResource;
use App\Models\PegawaiIzin;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreatePegawaiIzin extends CreateRecord
{
  protected static string $resource = PegawaiIzinResource::class;

  protected function mutateFormDataBeforeCreate(array $data): array
  {
    // Validasi: Cek apakah pegawai sudah memiliki surat izin aktif
    $suratAktif = PegawaiIzin::where('nip', $data['nip'])
      ->where('status', 'aktif')
      ->where('tanggal_selesai', '>=', now()->toDateString())
      ->orderBy('tanggal_selesai', 'desc')
      ->first();

    if ($suratAktif) {
      $jenisIzin = ucfirst($suratAktif->jenis_izin);
      $tanggalMulai = $suratAktif->tanggal_mulai->translatedFormat('d F Y');
      $tanggalSelesai = $suratAktif->tanggal_selesai->translatedFormat('d F Y');
      $besok = $suratAktif->tanggal_selesai->addDay()->translatedFormat('d F Y');

      Notification::make()
        ->danger()
        ->title('Pegawai Masih Memiliki Surat Izin Aktif')
        ->body("Pegawai {$data['nama_pegawai']} masih memiliki surat izin {$jenisIzin} yang berlaku dari {$tanggalMulai} sampai {$tanggalSelesai}. Surat izin baru dapat dibuat mulai tanggal {$besok}.")
        ->persistent()
        ->send();

      throw ValidationException::withMessages([
        'nip' => "Pegawai ini masih memiliki surat izin aktif hingga {$tanggalSelesai}.",
      ]);
    }

    $data['status'] = 'aktif';
    return $data;
  }

  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }
}
