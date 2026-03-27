<?php

namespace App\Filament\Staff\Resources\PegawaiIzinResource\Pages;

use App\Filament\Staff\Resources\PegawaiIzinResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePegawaiIzin extends CreateRecord
{
    protected static string $resource = PegawaiIzinResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = [
            ...$data,
            ...PegawaiIzinResource::resolveIdentityData(),
        ];

        $data['status'] = 'menunggu';

        return $data;
    }
}
