<?php

namespace App\Filament\Staff\Resources\BukuTamuResource\Pages;

use App\Filament\Staff\Resources\BukuTamuResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListBukuTamu extends ListRecords
{
    protected static string $resource = BukuTamuResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getFooter(): ?View
    {
        return view('filament.staff.pages.kunjungan-footer');
    }
}
