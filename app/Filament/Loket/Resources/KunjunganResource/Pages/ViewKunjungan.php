<?php

namespace App\Filament\Loket\Resources\KunjunganResource\Pages;

use App\Filament\Loket\Resources\KunjunganResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewKunjungan extends ViewRecord
{
  protected static string $resource = KunjunganResource::class;

  public function infolist(Infolist $infolist): Infolist
  {
    return $infolist->schema([
      Infolists\Components\Section::make('Data Tamu')
        ->columns(2)
        ->schema([
          Infolists\Components\TextEntry::make('nama_lengkap')->label('Nama Lengkap'),
          Infolists\Components\TextEntry::make('nik')->label('NIK'),
          Infolists\Components\TextEntry::make('instansi'),
          Infolists\Components\TextEntry::make('jabatan'),
          Infolists\Components\TextEntry::make('nomor_hp')->label('No. HP'),
          Infolists\Components\TextEntry::make('email'),
          Infolists\Components\TextEntry::make('kabupaten_kota')->label('Kab/Kota'),
          Infolists\Components\TextEntry::make('bagian_dituju')->label('Bagian Dituju'),
          Infolists\Components\TextEntry::make('keperluan')->columnSpanFull(),
        ]),
      Infolists\Components\Section::make('Status')
        ->columns(2)
        ->schema([
          Infolists\Components\TextEntry::make('status')
            ->badge()
            ->color(fn(string $state) => match ($state) {
              'menunggu' => 'warning',
              'diproses' => 'info',
              'selesai' => 'success',
              'ditolak' => 'danger',
              'dibatalkan' => 'gray',
              default => 'secondary',
            }),
          Infolists\Components\TextEntry::make('catatan')
            ->default('-'),
          Infolists\Components\TextEntry::make('created_at')
            ->label('Waktu Kunjungan')
            ->dateTime('d/m/Y H:i'),
        ]),
    ]);
  }
}
