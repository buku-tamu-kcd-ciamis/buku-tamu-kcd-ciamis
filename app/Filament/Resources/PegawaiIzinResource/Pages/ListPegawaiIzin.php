<?php

namespace App\Filament\Resources\PegawaiIzinResource\Pages;

use App\Filament\Resources\PegawaiIzinResource;
use Filament\Resources\Pages\ListRecords;

class ListPegawaiIzin extends ListRecords
{
  protected static string $resource = PegawaiIzinResource::class;

  protected function getHeaderActions(): array
  {
    return [];
  }
}
