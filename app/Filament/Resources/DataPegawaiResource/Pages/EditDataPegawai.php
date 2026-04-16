<?php

namespace App\Filament\Resources\DataPegawaiResource\Pages;

use App\Filament\Resources\DataPegawaiResource\Pages\Concerns\ManagesPegawaiLoginAccount;
use App\Filament\Resources\DataPegawaiResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditDataPegawai extends EditRecord
{
  use ManagesPegawaiLoginAccount;

  protected static string $resource = DataPegawaiResource::class;
  protected string $pendingLoginPassword = '';

  public function getMaxContentWidth(): Width | string | null
  {
    return Width::Full;
  }

  protected function getHeaderActions(): array
  {
    return [
      Actions\DeleteAction::make(),
    ];
  }

  protected function getRedirectUrl(): string
  {
    return $this->getResource()::getUrl('index');
  }

  protected function mutateFormDataBeforeSave(array $data): array
  {
    $this->pendingLoginPassword = $this->pullLoginPasswordFromFormData($data);
    $data = $this->normalizePegawaiEmailForPersistence($data, $this->record);

    return $data;
  }

  protected function afterSave(): void
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
}
