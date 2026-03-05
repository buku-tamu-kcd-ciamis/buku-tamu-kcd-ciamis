<?php

namespace App\Filament\Staff\Resources\PegawaiIzinResource\Pages;

use App\Filament\Staff\Resources\PegawaiIzinResource;
use Filament\Resources\Pages\ListRecords;

class ListPegawaiIzin extends ListRecords
{
    protected static string $resource = PegawaiIzinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make()
                ->label('Ajukan Izin'),
        ];
    }
}
