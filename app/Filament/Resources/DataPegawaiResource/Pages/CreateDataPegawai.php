<?php

namespace App\Filament\Resources\DataPegawaiResource\Pages;

use App\Filament\Resources\DataPegawaiResource\Pages\Concerns\ManagesPegawaiLoginAccount;
use App\Filament\Resources\DataPegawaiResource;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateDataPegawai extends CreateRecord
{
  use ManagesPegawaiLoginAccount;

  protected static string $resource = DataPegawaiResource::class;
  protected string $pendingLoginPassword = '';

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

  protected function mutateFormDataBeforeCreate(array $data): array
  {
    $this->pendingLoginPassword = $this->pullLoginPasswordFromFormData($data);

    return $data;
  }

  protected function afterCreate(): void
  {
    if ($this->pendingLoginPassword === '') {
      return;
    }

    $result = $this->syncLoginAccountPasswordForPegawai($this->record, $this->pendingLoginPassword);

    if (! $result['updated']) {
      Notification::make()
        ->warning()
        ->title('Password akun belum diubah')
        ->body($result['message'])
        ->send();

      return;
    }

    Notification::make()
      ->success()
      ->title($result['created'] ? 'Akun login dibuat' : 'Password akun diperbarui')
      ->body($result['message'])
      ->send();
  }

  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }
}
