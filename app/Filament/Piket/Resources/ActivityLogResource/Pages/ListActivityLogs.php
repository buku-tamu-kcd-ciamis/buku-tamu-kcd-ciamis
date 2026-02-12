<?php

namespace App\Filament\Piket\Resources\ActivityLogResource\Pages;

use App\Filament\Piket\Resources\ActivityLogResource;
use Filament\Resources\Pages\ListRecords;

class ListActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
