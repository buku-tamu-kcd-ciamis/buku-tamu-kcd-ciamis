<?php

namespace App\Filament\Staff\Resources\BukuTamuResource\Pages;

use App\Filament\Staff\Resources\BukuTamuResource;
use Filament\Resources\Pages\ViewRecord;

class ViewBukuTamu extends ViewRecord
{
    protected static string $resource = BukuTamuResource::class;

    protected function authorizeAccess(): void
    {
        parent::authorizeAccess();

        abort_unless(BukuTamuResource::canView($this->getRecord()), 403);
    }
}
