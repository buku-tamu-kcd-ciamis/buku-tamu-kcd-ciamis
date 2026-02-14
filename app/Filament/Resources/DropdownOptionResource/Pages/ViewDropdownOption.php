<?php

namespace App\Filament\Resources\DropdownOptionResource\Pages;

use App\Filament\Resources\DropdownOptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDropdownOption extends ViewRecord
{
  protected static string $resource = DropdownOptionResource::class;

  protected function getHeaderActions(): array
  {
    return [
      Actions\EditAction::make()
        ->label('Edit'),
      Actions\DeleteAction::make()
        ->label('Hapus')
        ->requiresConfirmation()
        ->modalHeading('Hapus Opsi Dropdown')
        ->modalDescription('Apakah Anda yakin? Data yang menggunakan opsi ini tetap tersimpan, tetapi opsi tidak akan muncul lagi di dropdown.')
        ->successNotificationTitle('Opsi berhasil dihapus')
        ->successRedirectUrl(DropdownOptionResource::getUrl('index')),
    ];
  }
}
