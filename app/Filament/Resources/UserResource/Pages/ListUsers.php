<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Exports\PegawaiTemplateExport;
use App\Imports\PegawaiImport;
use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('importPegawaiExcel')
                ->label('Import')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->modalHeading('Import Data Pegawai dari Excel')
                ->modalDescription('Unggah file Excel (.xlsx/.xls) untuk menambah atau memperbarui data pegawai.')
                ->form([
                    FileUpload::make('file')
                        ->label('File Excel')
                        ->disk('local')
                        ->directory('imports/pegawai')
                        ->preserveFilenames()
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->maxSize(5120)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $uploadedState = $data['file'] ?? null;
                    $relativePath = is_array($uploadedState)
                        ? (string) ($uploadedState[0] ?? '')
                        : (string) $uploadedState;

                    if ($relativePath === '' || ! Storage::disk('local')->exists($relativePath)) {
                        Notification::make()
                            ->danger()
                            ->title('File tidak ditemukan')
                            ->body('Silakan unggah ulang file Excel untuk proses import.')
                            ->send();

                        return;
                    }

                    try {
                        $importer = (new PegawaiImport())->import(Storage::disk('local')->path($relativePath));

                        if ($importer->hasErrors()) {
                            $errors = array_slice($importer->getErrors(), 0, 3);

                            Notification::make()
                                ->warning()
                                ->title('Import selesai dengan catatan')
                                ->body($importer->getSummary() . '. Contoh error: ' . implode(' | ', $errors))
                                ->send();
                        } else {
                            Notification::make()
                                ->success()
                                ->title('Import berhasil')
                                ->body($importer->getSummary())
                                ->send();
                        }
                    } catch (Throwable $exception) {
                        Notification::make()
                            ->danger()
                            ->title('Import gagal')
                            ->body('Terjadi kesalahan saat memproses file Excel.')
                            ->send();
                    } finally {
                        Storage::disk('local')->delete($relativePath);
                    }
                }),
            Actions\Action::make('downloadTemplatePegawai')
                ->label('Template')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(fn() => (new PegawaiTemplateExport())->download()),
            Actions\Action::make('exportPegawaiExcel')
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(route('pegawai.export-excel')),
            Actions\CreateAction::make()
                ->label('Buat')
                ->color('primary'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua User')
                ->badge($this->countAllNonSuperUsers()),
            'kepala_cabdin' => Tab::make('Kepala Cabang Dinas')
                ->badge($this->countByRole('Kepala Cabang Dinas'))
                ->badgeColor('warning')
                ->modifyQueryUsing(fn(Builder $query): Builder => $query->whereHas('role_user', fn(Builder $roleQuery): Builder => $roleQuery->where('name', 'Kepala Cabang Dinas'))),
            'piket' => Tab::make('Piket')
                ->badge($this->countByRole('Piket'))
                ->badgeColor('success')
                ->modifyQueryUsing(fn(Builder $query): Builder => $query->whereHas('role_user', fn(Builder $roleQuery): Builder => $roleQuery->where('name', 'Piket'))),
            'staff' => Tab::make('Staff')
                ->badge($this->countByRole('Staff'))
                ->badgeColor('info')
                ->modifyQueryUsing(fn(Builder $query): Builder => $query->whereHas('role_user', fn(Builder $roleQuery): Builder => $roleQuery->where('name', 'Staff'))),
        ];
    }

    protected function countAllNonSuperUsers(): int
    {
        return $this->baseNonSuperQuery()->count();
    }

    protected function countByRole(?string $role = null): int
    {
        if ($role === null) {
            return $this->countAllNonSuperUsers();
        }

        return $this->baseNonSuperQuery()
            ->whereHas('role_user', fn(Builder $roleQuery): Builder => $roleQuery->where('name', $role))
            ->count();
    }

    protected function baseNonSuperQuery(): Builder
    {
        return User::query()
            ->where(function (Builder $userQuery): void {
                $userQuery->whereDoesntHave('role_user', function (Builder $roleQuery): void {
                    $roleQuery->where('name', 'Super Admin');
                })
                ->orWhereNull('role_user_id');
            });
    }
}
