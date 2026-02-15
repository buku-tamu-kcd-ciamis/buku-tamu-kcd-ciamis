<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->hidden(fn(): bool => !$this->record->isDeletable())
                ->before(function (Actions\DeleteAction $action) {
                    if (!$this->record->isDeletable()) {
                        $reason = $this->record->hasRole('Super Admin')
                            ? 'User dengan role Super Admin tidak dapat dihapus.'
                            : 'Minimal harus ada 1 user dengan role ' . ($this->record->role_user->name ?? 'ini') . '. Ini adalah satu-satunya user dengan role tersebut.';

                        Notification::make()
                            ->danger()
                            ->title('Tidak dapat menghapus user!')
                            ->body($reason)
                            ->send();

                        $action->cancel();
                    }
                }),
        ];
    }
}
