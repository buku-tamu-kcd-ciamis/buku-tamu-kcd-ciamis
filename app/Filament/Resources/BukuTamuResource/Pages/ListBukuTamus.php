<?php

namespace App\Filament\Resources\BukuTamuResource\Pages;

use App\Filament\Resources\BukuTamuResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListBukuTamus extends ListRecords
{
    protected static string $resource = BukuTamuResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getFooter(): ?View
    {
        return view('filament.pages.buku-tamu-footer');
    }
}
