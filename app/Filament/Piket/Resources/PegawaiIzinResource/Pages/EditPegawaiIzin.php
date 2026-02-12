<?php

namespace App\Filament\Piket\Resources\PegawaiIzinResource\Pages;

use App\Filament\Piket\Resources\PegawaiIzinResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPegawaiIzin extends EditRecord
{
  protected static string $resource = PegawaiIzinResource::class;

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
