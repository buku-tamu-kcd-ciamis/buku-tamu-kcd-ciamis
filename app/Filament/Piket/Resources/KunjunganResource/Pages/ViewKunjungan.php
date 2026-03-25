<?php

namespace App\Filament\Piket\Resources\KunjunganResource\Pages;

use App\Filament\Piket\Resources\KunjunganResource;
use App\Models\BukuTamu;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;

class ViewKunjungan extends ViewRecord
{
  protected static string $resource = KunjunganResource::class;

  public function infolist(Schema $schema): Schema
  {
    return $schema->components([

      // === Header: Foto Selfie + Info Utama ===
      Section::make()
        ->components([
          Grid::make(3)
            ->components([
              // Foto Selfie (kolom kiri)
              Group::make([
                Infolists\Components\ImageEntry::make('foto_selfie'),
              ])->columnSpan(1),

              // Info utama (kolom tengah-kanan)
              Group::make([
                Infolists\Components\TextEntry::make('nama_lengkap')
                  ->size('lg'),
                Grid::make(2)
                  ->components([
                    Infolists\Components\TextEntry::make('jenis_id')
                      ->icon('heroicon-o-identification'),
                    Infolists\Components\TextEntry::make('nik')
                      ->icon('heroicon-o-finger-print')
                      ->copyable(),
                    Infolists\Components\TextEntry::make('instansi')
                      ->icon('heroicon-o-building-office-2')
                      ->placeholder('-'),
                    Infolists\Components\TextEntry::make('jabatan')
                      ->icon('heroicon-o-briefcase')
                      ->placeholder('-'),
                    Infolists\Components\TextEntry::make('nomor_hp')
                      ->icon('heroicon-o-phone')
                      ->formatStateUsing(function ($state) {
                        if (!$state)
                          return '-';
                        $cleaned = preg_replace('/[^0-9]/', '', $state);
                        if (str_starts_with($cleaned, '0')) {
                          $cleaned = substr($cleaned, 1);
                        }
                        return '+62' . $cleaned;
                      })
                      ->copyable(),
                    Infolists\Components\TextEntry::make('email')
                      ->icon('heroicon-o-envelope')
                      ->copyable()
                      ->placeholder('-'),
                  ]),
              ])->columnSpan(2),
            ]),
        ]),

      // === Informasi Kunjungan ===
      Section::make('Informasi Kunjungan')
        ->icon('heroicon-o-clipboard-document-list')
        ->columns(2)
        ->components([
          Infolists\Components\TextEntry::make('kabupaten_kota')
            ->icon('heroicon-o-map-pin'),
          Infolists\Components\TextEntry::make('staff_dituju')
            ->icon('heroicon-o-building-office'),
          Infolists\Components\TextEntry::make('keperluan')
            ->icon('heroicon-o-document-text'),
          Infolists\Components\TextEntry::make('created_at')
            ->icon('heroicon-o-clock')
            ->dateTime('d F Y, H:i:s'),
        ]),

      // === Status ===
      Section::make('Status Kunjungan')
        ->icon('heroicon-o-signal')
        ->columns(2)
        ->components([
          Infolists\Components\TextEntry::make('status')
            ->badge()
            ->formatStateUsing(fn(string $state) => BukuTamu::STATUS_LABELS[$state] ?? ucfirst($state))
            ->color(fn(string $state) => match ($state) {
              'menunggu' => 'warning',
              'diproses' => 'info',
              'selesai' => 'success',
              'ditolak' => 'danger',
              'dibatalkan' => 'gray',
              default => 'secondary',
            }),
          Infolists\Components\TextEntry::make('nama_penerima')
            ->icon('heroicon-o-user')
            ->placeholder('Belum ada penerima'),
          Infolists\Components\TextEntry::make('catatan')
            ->placeholder('Tidak ada catatan'),
        ]),

      // === Dokumen: Foto Penerimaan + Tanda Tangan ===
      Section::make('Dokumen')
        ->icon('heroicon-o-camera')
        ->columns(2)
        ->components([
          Infolists\Components\ImageEntry::make('foto_penerimaan'),
          Infolists\Components\ImageEntry::make('tanda_tangan'),
        ]),
    ]);
  }
}
