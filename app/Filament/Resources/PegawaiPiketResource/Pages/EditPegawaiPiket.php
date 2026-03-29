<?php

namespace App\Filament\Resources\PegawaiPiketResource\Pages;

use App\Filament\Resources\PegawaiPiketResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditPegawaiPiket extends EditRecord
{
    protected static string $resource = PegawaiPiketResource::class;

    public function getMaxContentWidth(): Width | string | null
    {
        return Width::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
