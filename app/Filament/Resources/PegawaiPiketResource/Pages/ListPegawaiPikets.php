<?php

namespace App\Filament\Resources\PegawaiPiketResource\Pages;

use App\Filament\Resources\PegawaiPiketResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPegawaiPikets extends ListRecords
{
    protected static string $resource = PegawaiPiketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Pegawai'),
            Actions\Action::make('print')
                ->label('Cetak Laporan')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(route('pegawai-piket.print'))
                ->openUrlInNewTab(),
        ];
    }
}
