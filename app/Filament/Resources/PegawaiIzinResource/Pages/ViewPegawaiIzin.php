<?php

namespace App\Filament\Resources\PegawaiIzinResource\Pages;

use App\Filament\Resources\PegawaiIzinResource;
use App\Models\PegawaiIzin;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class ViewPegawaiIzin extends ViewRecord
{
  protected static string $resource = PegawaiIzinResource::class;

  protected function getHeaderActions(): array
  {
    return [];
  }

  public function infolist(Schema $schema): Schema
  {
    return $schema->components([
      // === Informasi Pegawai ===
      Section::make('Informasi Pegawai')
        ->icon('heroicon-o-user')
        ->columns(2)
        ->components([
          Infolists\Components\TextEntry::make('nama_pegawai')
            ->label('Nama Pegawai')
            ->size('lg')
            ->icon('heroicon-o-user'),
          Infolists\Components\TextEntry::make('nip')
            ->label('NIP')
            ->icon('heroicon-o-identification')
            ->copyable(),
          Infolists\Components\TextEntry::make('nomor_hp')
            ->label('Nomor HP')
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

      // === Informasi Izin ===
      Section::make('Informasi Izin')
        ->icon('heroicon-o-calendar-days')
        ->columns(2)
        ->components([
          Infolists\Components\TextEntry::make('jenis_izin')
            ->label('Jenis Izin')
            ->badge()
            ->color('info')
            ->formatStateUsing(fn($state) => PegawaiIzin::JENIS_IZIN_LABELS[$state] ?? $state)
            ->icon('heroicon-o-clipboard-document-list'),
          Infolists\Components\TextEntry::make('status')
            ->label('Status')
            ->badge()
            ->color(fn($state) => match ($state) {
              PegawaiIzin::STATUS_DISETUJUI, PegawaiIzin::STATUS_AKTIF => 'success',
              PegawaiIzin::STATUS_MENUNGGU => 'warning',
              PegawaiIzin::STATUS_DITOLAK => 'danger',
              PegawaiIzin::STATUS_SELESAI => 'gray',
              default => 'gray',
            })
            ->formatStateUsing(fn($state) => PegawaiIzin::STATUS_LABELS[$state] ?? ucfirst($state))
            ->icon('heroicon-o-signal'),
          Infolists\Components\TextEntry::make('tanggal_mulai')
            ->label('Tanggal Mulai')
            ->date('d F Y')
            ->icon('heroicon-o-calendar'),
          Infolists\Components\TextEntry::make('tanggal_selesai')
            ->label('Tanggal Selesai')
            ->date('d F Y')
            ->icon('heroicon-o-calendar'),
          Infolists\Components\TextEntry::make('nama_piket')
            ->label('Nama Piket')
            ->icon('heroicon-o-user-circle')
            ->placeholder('-'),
          Infolists\Components\TextEntry::make('keterangan')
            ->label('Keterangan')
            ->icon('heroicon-o-document-text')
            ->placeholder('-'),
        ]),

      Section::make('Verifikasi Kepala KCD')
        ->icon('heroicon-o-check-badge')
        ->columns(2)
        ->components([
          Infolists\Components\TextEntry::make('diverifikasi_oleh')
            ->label('Diverifikasi Oleh')
            ->placeholder('-')
            ->icon('heroicon-o-user-circle'),
          Infolists\Components\TextEntry::make('diverifikasi_pada')
            ->label('Diverifikasi Pada')
            ->dateTime('d F Y, H:i:s')
            ->placeholder('-')
            ->icon('heroicon-o-clock'),
          Infolists\Components\TextEntry::make('catatan_verifikasi')
            ->label('Catatan Verifikasi')
            ->placeholder('-')
            ->icon('heroicon-o-chat-bubble-left-right')
            ->columnSpanFull(),
        ]),

      // === Tanda Tangan Piket ===
      Section::make('Tanda Tangan Piket')
        ->icon('heroicon-o-pencil-square')
        ->components([
          Infolists\Components\ImageEntry::make('tanda_tangan_piket')
            ->label('Tanda Tangan Piket')
            ->disk('public')
            ->extraAttributes([
              'class' => 'bt-signature-entry',
            ]),
        ]),

      // === Informasi Sistem ===
      Section::make('Informasi Sistem')
        ->icon('heroicon-o-information-circle')
        ->columns(2)
        ->collapsed()
        ->components([
          Infolists\Components\TextEntry::make('created_at')
            ->label('Dibuat Pada')
            ->dateTime('d F Y, H:i:s')
            ->icon('heroicon-o-clock'),
          Infolists\Components\TextEntry::make('updated_at')
            ->label('Diperbarui Pada')
            ->dateTime('d F Y, H:i:s')
            ->icon('heroicon-o-clock'),
        ]),
    ]);
  }
}
