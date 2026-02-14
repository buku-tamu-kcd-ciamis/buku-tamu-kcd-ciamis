<?php

namespace App\Filament\Resources\DataPegawaiResource\Pages;

use App\Filament\Resources\DataPegawaiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDataPegawai extends ListRecords
{
  protected static string $resource = DataPegawaiResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\CreateAction::make()
        ->label('Tambah Pegawai')
        ->icon('heroicon-o-plus'),
      Actions\Action::make('print')
        ->label('Cetak Laporan')
        ->icon('heroicon-o-printer')
        ->color('success')
        ->url(fn() => route('data-pegawai.print'))
        ->openUrlInNewTab(),
    ];
  }
}
