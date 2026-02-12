<?php

namespace App\Filament\Piket\Resources\PegawaiIzinResource\Pages;

use App\Filament\Piket\Resources\PegawaiIzinResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePegawaiIzin extends CreateRecord
{
  protected static string $resource = PegawaiIzinResource::class;

  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }
}
