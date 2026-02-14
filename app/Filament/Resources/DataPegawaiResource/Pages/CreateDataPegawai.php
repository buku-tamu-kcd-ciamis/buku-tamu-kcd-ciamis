<?php

namespace App\Filament\Resources\DataPegawaiResource\Pages;

use App\Filament\Resources\DataPegawaiResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDataPegawai extends CreateRecord
{
  protected static string $resource = DataPegawaiResource::class;

  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }
}
