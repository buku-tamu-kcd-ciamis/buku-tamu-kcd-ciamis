<?php

namespace App\Filament\Resources\DataPegawaiResource\Pages;

use App\Filament\Resources\DataPegawaiResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateDataPegawai extends CreateRecord
{
  protected static string $resource = DataPegawaiResource::class;

  public function getMaxContentWidth(): Width | string | null
  {
    return Width::Full;
  }

  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }
}
