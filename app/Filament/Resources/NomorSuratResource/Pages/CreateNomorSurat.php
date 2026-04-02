<?php

namespace App\Filament\Resources\NomorSuratResource\Pages;

use App\Filament\Resources\NomorSuratResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateNomorSurat extends CreateRecord
{
  protected static string $resource = NomorSuratResource::class;

  protected string $view = 'filament.resources.nomor-surat-resource.pages.create-nomor-surat';

  public function getTitle(): string
  {
    return 'Tambah Pengaturan Nomor Surat';
  }

  protected function getCreateFormAction(): Action
  {
    return parent::getCreateFormAction()->label('Simpan Pengaturan');
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
    return 'Pengaturan nomor surat berhasil ditambahkan';
  }

  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }
}
