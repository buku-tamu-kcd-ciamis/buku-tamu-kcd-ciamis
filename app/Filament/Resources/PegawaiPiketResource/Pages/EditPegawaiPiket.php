<?php

namespace App\Filament\Resources\PegawaiPiketResource\Pages;

use App\Filament\Resources\PegawaiPiketResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPegawaiPiket extends EditRecord
{
    protected static string $resource = PegawaiPiketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
