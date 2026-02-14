<?php

namespace App\Filament\Resources\DropdownOptionResource\Pages;

use App\Filament\Resources\DropdownOptionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDropdownOption extends EditRecord
{
  protected static string $resource = DropdownOptionResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\DeleteAction::make()
        ->label('Hapus'),
    ];
  }

  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }

  protected function getSavedNotificationTitle(): ?string
  {
    return 'Opsi dropdown berhasil diperbarui';
  }
}
