<?php

namespace App\Filament\Resources\DropdownOptionResource\Pages;

use App\Filament\Resources\DropdownOptionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDropdownOption extends CreateRecord
{
  protected static string $resource = DropdownOptionResource::class;

  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }

  protected function getCreatedNotificationTitle(): ?string
  {
    return 'Opsi dropdown berhasil ditambahkan';
  }
}
