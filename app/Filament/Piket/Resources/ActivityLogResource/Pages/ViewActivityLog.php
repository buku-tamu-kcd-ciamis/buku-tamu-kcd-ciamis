<?php

namespace App\Filament\Piket\Resources\ActivityLogResource\Pages;

use App\Filament\Piket\Resources\ActivityLogResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class ViewActivityLog extends ViewRecord
{
  protected static string $resource = ActivityLogResource::class;

  public function infolist(Schema $schema): Schema
  {
    return $schema->components([
      Section::make('Informasi Log Aktivitas')
        ->components([
          Grid::make(2)
            ->components([
              Infolists\Components\TextEntry::make('created_at')
                ->dateTime('d/m/Y H:i:s'),
              Infolists\Components\TextEntry::make('causer.name'),
              Infolists\Components\TextEntry::make('log_name')
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
              Infolists\Components\TextEntry::make('description'),
              Infolists\Components\TextEntry::make('subject_type'),
              Infolists\Components\TextEntry::make('subject_id'),
            ]),
        ]),
      Section::make('Properties')
        ->components([
          Infolists\Components\TextEntry::make('properties')
            ->formatStateUsing(fn($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : (string) $state),
        ])
        ->collapsed()
        ->collapsible(),
    ]);
  }
}
