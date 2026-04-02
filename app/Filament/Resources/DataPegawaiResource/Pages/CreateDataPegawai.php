<?php

namespace App\Filament\Resources\DataPegawaiResource\Pages;

use App\Filament\Resources\DataPegawaiResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateDataPegawai extends CreateRecord
{
  protected static string $resource = DataPegawaiResource::class;

  public function getMaxContentWidth(): Width | string | null
  {
    return Width::Full;
  }

  public function getTitle(): string
  {
    return 'Tambah Data Pegawai';
  }

  protected function getCreateFormAction(): Action
  {
    return parent::getCreateFormAction()->label('Simpan');
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
    return 'Data pegawai berhasil ditambahkan';
  }

  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }
}
