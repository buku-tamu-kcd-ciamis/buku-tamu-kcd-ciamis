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
    $data = $this->normalizePegawaiEmailForPersistence($data);

    return $data;
  }

  protected function afterCreate(): void
  {
    $isUsingDefaultPassword = $this->pendingLoginPassword === '';
    $passwordToApply = $isUsingDefaultPassword
      ? $this->resolveDefaultPasswordForPegawai($this->record)
      : $this->pendingLoginPassword;

    $result = $this->syncLoginAccountPasswordForPegawai($this->record, $passwordToApply);

    if (! $result['updated']) {
      Notification::make()
        ->warning()
        ->title('Akun login belum diproses')
        ->body($result['message'])
        ->send();

      return;
    }

    Notification::make()
      ->success()
      ->title($result['created']
        ? ($isUsingDefaultPassword ? 'Akun login dibuat otomatis' : 'Akun login dibuat')
        : ($isUsingDefaultPassword ? 'Password akun disetel default' : 'Password akun diperbarui'))
      ->body($isUsingDefaultPassword
        ? ($result['message'] . ' Password default: ' . $passwordToApply . '.')
        : $result['message'])
      ->send();
  }

  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }
}
