<?php

namespace App\Filament\Resources\NomorSuratResource\Pages;

use App\Filament\Resources\NomorSuratResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNomorSurat extends CreateRecord
{
  protected static string $resource = NomorSuratResource::class;

  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }
}
