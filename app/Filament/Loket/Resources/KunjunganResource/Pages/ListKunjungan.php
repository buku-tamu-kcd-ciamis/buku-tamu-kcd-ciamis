<?php

namespace App\Filament\Loket\Resources\KunjunganResource\Pages;

use App\Filament\Loket\Resources\KunjunganResource;
use Filament\Resources\Pages\ListRecords;

class ListKunjungan extends ListRecords
{
  protected static string $resource = KunjunganResource::class;

  protected function getHeaderActions(): array
  {
    return [];
  }
}
