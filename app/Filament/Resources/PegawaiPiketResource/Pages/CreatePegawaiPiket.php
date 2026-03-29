<?php

namespace App\Filament\Resources\PegawaiPiketResource\Pages;

use App\Filament\Resources\PegawaiPiketResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreatePegawaiPiket extends CreateRecord
{
    protected static string $resource = PegawaiPiketResource::class;

    public function getMaxContentWidth(): Width | string | null
    {
        return Width::Full;
    }
}
