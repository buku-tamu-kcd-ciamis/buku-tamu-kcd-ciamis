<?php

namespace App\Filament\Resources\DropdownOptionResource\Pages;

use App\Filament\Resources\DropdownOptionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDropdownOption extends EditRecord
{
  protected static string $resource = DropdownOptionResource::class;

  protected function mutateFormDataBeforeSave(array $data): array
  {
    $value = trim((string) ($data['value'] ?? $this->record->value ?? ''));

    if ($value === '') {
      $value = trim((string) ($data['label'] ?? $this->record->label ?? ''));
    }

    $data['value'] = $value;
    $data['label'] = $value;

    if (! array_key_exists('sort_order', $data) && $this->record) {
      $data['sort_order'] = (int) $this->record->sort_order;
    }

    return $data;
  }

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
