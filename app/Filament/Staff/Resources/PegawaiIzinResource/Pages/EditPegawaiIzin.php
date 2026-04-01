<?php

namespace App\Filament\Staff\Resources\PegawaiIzinResource\Pages;

use App\Filament\Staff\Resources\PegawaiIzinResource;
use Filament\Resources\Pages\EditRecord;

class EditPegawaiIzin extends EditRecord
{
    protected static string $resource = PegawaiIzinResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
