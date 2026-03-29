<?php

namespace App\Filament\Staff\Resources\PegawaiIzinResource\Pages;

use App\Filament\Staff\Resources\PegawaiIzinResource;
use App\Models\PegawaiIzin;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreatePegawaiIzin extends CreateRecord
{
    protected static string $resource = PegawaiIzinResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = [
            ...$data,
            ...PegawaiIzinResource::resolveIdentityData(),
        ];

        $izinBerjalan = PegawaiIzin::query()
            ->when(filled($data['nip']), function ($query) use ($data) {
                $query->where('nip', $data['nip']);
            }, function ($query) use ($data) {
                $query->where('nama_pegawai', $data['nama_pegawai']);
            })
            ->whereIn('status', [
                PegawaiIzin::STATUS_MENUNGGU,
                PegawaiIzin::STATUS_DISETUJUI,
                PegawaiIzin::STATUS_AKTIF,
            ])
            ->whereDate('tanggal_selesai', '>=', now()->toDateString())
            ->orderByDesc('tanggal_selesai')
            ->first();

        if ($izinBerjalan) {
            $statusLabel = PegawaiIzin::STATUS_LABELS[$izinBerjalan->status] ?? ucfirst($izinBerjalan->status);
            $tanggalSelesai = $izinBerjalan->tanggal_selesai?->translatedFormat('d F Y') ?? '-';

            Notification::make()
                ->warning()
                ->title('Masih ada pengajuan/proses izin berjalan')
                ->body("Pengajuan sebelumnya masih berstatus {$statusLabel} sampai {$tanggalSelesai}.")
                ->send();

            throw ValidationException::withMessages([
                'jenis_izin' => "Pengajuan belum bisa dibuat karena izin sebelumnya masih berstatus {$statusLabel}.",
            ]);
        }

        $data['status'] = PegawaiIzin::STATUS_MENUNGGU;

        return $data;
    }
}
