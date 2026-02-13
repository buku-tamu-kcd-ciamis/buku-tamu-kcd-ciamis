<?php

namespace App\Filament\Piket\Resources\ActivityLogResource\Pages;

use App\Filament\Piket\Resources\ActivityLogResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewActivityLog extends ViewRecord
{
  protected static string $resource = ActivityLogResource::class;

  public function infolist(Infolist $infolist): Infolist
  {
    return $infolist->schema([
      Infolists\Components\Section::make('Informasi Log Aktivitas')
        ->schema([
          Infolists\Components\Grid::make(2)
            ->schema([
              Infolists\Components\TextEntry::make('created_at')
                ->label('Waktu')
                ->dateTime('d/m/Y H:i:s'),
              Infolists\Components\TextEntry::make('causer.name')
                ->label('User')
                ->default('System'),
              Infolists\Components\TextEntry::make('log_name')
                ->label('Kategori')
                ->badge()
                ->formatStateUsing(fn(string $state): string => match ($state) {
                  'buku_tamu' => 'Buku Tamu',
                  'pegawai_izin' => 'Izin Pegawai',
                  'auth' => 'Login/Logout',
                  default => ucfirst(str_replace('_', ' ', $state)),
                })
                ->color(fn(string $state): string => match ($state) {
                  'buku_tamu' => 'success',
                  'pegawai_izin' => 'info',
                  'auth' => 'warning',
                  default => 'gray',
                }),
              Infolists\Components\TextEntry::make('description')
                ->label('Deskripsi Aktivitas')
                ->columnSpanFull(),
              Infolists\Components\TextEntry::make('subject_type')
                ->label('Tipe Subject')
                ->default('-'),
              Infolists\Components\TextEntry::make('subject_id')
                ->label('ID Subject')
                ->default('-'),
            ]),
        ]),
      Infolists\Components\Section::make('Properties')
        ->schema([
          Infolists\Components\ViewEntry::make('properties')
            ->label('')
            ->view('filament.infolists.components.json-viewer'),
        ])
        ->collapsed()
        ->collapsible(),
    ]);
  }
}
