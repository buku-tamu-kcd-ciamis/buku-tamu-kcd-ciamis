<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        UserResource::validateSingleKepalaCabdin(
            (string) ($data['role_user_id'] ?? ''),
            (string) ($this->record?->id ?? ''),
            (string) ($this->record?->role_user_id ?? ''),
        );

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->hidden(fn(): bool => !(Auth::user()?->hasRole('Super Admin') ?? false) || !$this->record->isDeletable())
                ->before(function (Actions\DeleteAction $action) {
                    if (!(Auth::user()?->hasRole('Super Admin') ?? false)) {
                        Notification::make()
                            ->danger()
                            ->title('Akses ditolak!')
                            ->body('Hanya Super Admin yang dapat menghapus akun user.')
                            ->send();

                        $action->cancel();

                        return;
                    }

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
