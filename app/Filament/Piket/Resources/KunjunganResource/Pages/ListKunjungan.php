<?php

namespace App\Filament\Piket\Resources\KunjunganResource\Pages;

use App\Filament\Piket\Resources\KunjunganResource;
use Filament\Resources\Pages\ListRecords;

class ListKunjungan extends ListRecords
{
  protected static string $resource = KunjunganResource::class;

  protected function getHeaderActions(): array
  {
    return [];
  }
}
