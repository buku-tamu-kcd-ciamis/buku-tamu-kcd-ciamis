<?php

namespace App\Filament\Resources\FaqResource\Pages;

use App\Filament\Resources\FaqResource;
use App\Models\Faq;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateFaq extends CreateRecord
{
  protected static string $resource = FaqResource::class;

  protected string $view = 'filament.resources.faq-resource.pages.create-faq';

  public function getTitle(): string
  {
    return 'Tambah FAQ';
  }

  protected function getCreateFormAction(): Action
  {
    return parent::getCreateFormAction()->label('Simpan FAQ');
  }

  protected function getCreateAnotherFormAction(): Action
  {
    return parent::getCreateAnotherFormAction()->label('Simpan & Tambah Lagi');
  }

  protected function getCancelFormAction(): Action
  {
    return parent::getCancelFormAction()->label('Batal');
  }

  protected function getCreatedNotificationTitle(): ?string
  {
    return 'FAQ berhasil ditambahkan';
  }

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
