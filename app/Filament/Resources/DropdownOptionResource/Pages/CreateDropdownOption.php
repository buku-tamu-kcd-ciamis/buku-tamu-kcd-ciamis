<?php

namespace App\Filament\Resources\DropdownOptionResource\Pages;

use App\Filament\Resources\DropdownOptionResource;
use App\Models\DropdownOption;
use Filament\Resources\Pages\CreateRecord;

class CreateDropdownOption extends CreateRecord
{
  protected static string $resource = DropdownOptionResource::class;

  protected string $view = 'filament.resources.dropdown-option-resource.pages.create-dropdown-option';

  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }

  protected function mutateFormDataBeforeCreate(array $data): array
  {
    $value = trim((string) ($data['value'] ?? ''));
    $label = trim((string) ($data['label'] ?? ''));
    $category = trim((string) ($data['category'] ?? ''));

    if ($value === '' && $label !== '') {
      $value = $label;
    }

    if ($label === '' && $value !== '') {
      $label = $value;
    }

    $data['value'] = $value;
    $data['label'] = $label;

    if (! filled($data['sort_order'] ?? null)) {
      $lastSortOrder = $category !== ''
        ? (int) DropdownOption::query()->where('category', $category)->max('sort_order')
        : 0;

      $data['sort_order'] = $lastSortOrder + 1;
    }

    return $data;
  }

  protected function getCreatedNotificationTitle(): ?string
  {
    return 'Opsi dropdown berhasil ditambahkan';
  }
}
