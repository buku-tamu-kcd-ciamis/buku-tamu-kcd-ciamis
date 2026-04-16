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

      // === Informasi Tamu (full width) ===
      Section::make()
        ->columnSpanFull()
        ->components([
          Grid::make(3)
            ->components([
              Group::make([
                Infolists\Components\ImageEntry::make('foto_selfie')
                  ->label('Foto selfie')
                  ->disk('public'),
              ])->columnSpan(1),
              Group::make([
                Infolists\Components\TextEntry::make('nama_lengkap')
                  ->size('lg'),
                Grid::make(2)
                  ->components([
                    Infolists\Components\TextEntry::make('jenis_id')
                      ->icon('heroicon-o-identification'),
                    Infolists\Components\TextEntry::make('nik')
                      ->label(fn($record): string => filled($record?->jenis_id) ? strtoupper((string) $record->jenis_id) : 'Nomor ID')
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

      // === Baris Tengah: Status + Informasi Kunjungan ===
      Grid::make([
        'default' => 1,
        'lg' => 2,
      ])
        ->columnSpanFull()
        ->components([
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
              Infolists\Components\TextEntry::make('diselesaikan_oleh')
                ->label('Diselesaikan oleh')
                ->icon('heroicon-o-user-circle')
                ->placeholder('Belum diselesaikan'),
              Infolists\Components\TextEntry::make('diselesaikan_pada')
                ->label('Jam selesai')
                ->icon('heroicon-o-clock')
                ->dateTime('d F Y, H:i:s')
                ->placeholder('Belum diselesaikan'),
              Infolists\Components\TextEntry::make('catatan')
                ->placeholder('Tidak ada catatan'),
            ]),
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
        ]),

      // === Dokumen (full width) ===
      Section::make('Dokumen')
        ->icon('heroicon-o-camera')
        ->columnSpanFull()
        ->columns(2)
        ->components([
          Infolists\Components\ImageEntry::make('foto_penerimaan')
            ->label('Foto penerimaan')
            ->disk('public'),
          Infolists\Components\ImageEntry::make('tanda_tangan')
            ->label('Tanda tangan')
            ->disk('public'),
        ]),
    ]);
  }
}
