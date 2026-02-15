<?php

namespace App\Filament\Resources\FaqResource\Pages;

use App\Filament\Resources\FaqResource;
use App\Models\Faq;
use Filament\Resources\Pages\CreateRecord;

class CreateFaq extends CreateRecord
{
  protected static string $resource = FaqResource::class;

  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }

  protected function mutateFormDataBeforeCreate(array $data): array
  {
    // Auto-increment sort_order based on target
    $target = $data['target'] ?? 'semua';
    $maxSortOrder = Faq::where('target', $target)->max('sort_order') ?? 0;
    $data['sort_order'] = $maxSortOrder + 1;

    return $data;
  }
}
