<?php

namespace App\Filament\Piket\Resources\KunjunganResource\Pages;

use App\Filament\Piket\Resources\KunjunganResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListKunjungan extends ListRecords
{
  protected static string $resource = KunjunganResource::class;

  protected function getHeaderActions(): array
  {
    return [];
  }

  public function getFooter(): ?View
  {
    return view('filament.piket.pages.kunjungan-footer');
  }
}
