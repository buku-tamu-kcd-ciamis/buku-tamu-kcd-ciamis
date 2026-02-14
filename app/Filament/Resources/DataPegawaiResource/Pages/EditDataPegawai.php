<?php

namespace App\Filament\Resources\DataPegawaiResource\Pages;

use App\Filament\Resources\DataPegawaiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDataPegawai extends EditRecord
{
  protected static string $resource = DataPegawaiResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\DeleteAction::make(),
    ];
  }

  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }
}
