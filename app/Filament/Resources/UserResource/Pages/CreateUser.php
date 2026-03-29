<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    public function mutateFormDataBeforeCreate(array $data): array
    {
        UserResource::validateSingleKepalaCabdin((string) ($data['role_user_id'] ?? ''));

        $data['author_id'] = auth()->user()->id;

        return $data;
    }
}
