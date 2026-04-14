<?php

namespace App\Filament\Resources\PegawaiPiketResource\Pages;

use App\Exports\PegawaiPiketTemplateExport;
use App\Filament\Resources\PegawaiPiketResource;
use App\Imports\PegawaiPiketImport;
use App\Models\RoleUser;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ListPegawaiPikets extends ListRecords
{
    protected static string $resource = PegawaiPiketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('importPegawaiPiketExcel')
                ->label('Import')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->modalHeading('Import Data Pegawai Piket dari Excel')
                ->modalDescription('Unggah file Excel (.xlsx/.xls) untuk menambah atau memperbarui data pegawai piket.')
                ->schema([
                    FileUpload::make('file')
                        ->label('File Excel')
                        ->disk('local')
                        ->directory('imports/pegawai-piket')
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
                        $importer = (new PegawaiPiketImport())->import(Storage::disk('local')->path($relativePath));
                        $syncResult = $this->syncPiketUsersByImportedRows($importer->getProcessedRows());

                        if ($importer->hasErrors()) {
                            $errors = array_slice($importer->getErrors(), 0, 3);

                            Notification::make()
                                ->warning()
                                ->title('Import selesai dengan catatan')
                                ->body($importer->getSummary() . '. Sinkron user: ' . $syncResult['summary'] . '. Contoh error: ' . implode(' | ', $errors))
                                ->send();
                        } else {
                            Notification::make()
                                ->success()
                                ->title('Import berhasil')
                                ->body($importer->getSummary() . '. Sinkron user: ' . $syncResult['summary'])
                                ->send();
                        }
                    } catch (Throwable $exception) {
                        Log::error('Import Data Pegawai Piket gagal.', [
                            'exception' => $exception,
                            'message' => $exception->getMessage(),
                        ]);

                        Notification::make()
                            ->danger()
                            ->title('Import gagal')
                            ->body('Gagal import. Periksa format file dan gunakan template yang disediakan.')
                            ->send();
                    } finally {
                        Storage::disk('local')->delete($relativePath);
                    }
                }),
            Actions\Action::make('downloadTemplatePegawaiPiket')
                ->label('Template')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->action(fn() => (new PegawaiPiketTemplateExport())->download()),
            Actions\Action::make('exportPegawaiPiketPdf')
                ->label('Export')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->url(route('pegawai-piket.print'))
                ->openUrlInNewTab(true),
            Actions\CreateAction::make()
                ->label('Buat')
                ->color('success'),
        ];
    }

    protected function syncPiketUsersByImportedRows(array $rows): array
    {
        if ($rows === []) {
            return [
                'created' => 0,
                'updated' => 0,
                'summary' => '0 user dibuat, 0 user diperbarui, 0 user dilewati',
            ];
        }

        $piketRoleId = RoleUser::query()
            ->where('name', 'Piket')
            ->value('id');

        if (! $piketRoleId) {
            return [
                'created' => 0,
                'updated' => 0,
                'summary' => 'Role Piket tidak ditemukan, sinkron user dilewati',
            ];
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $name = trim((string) ($row['label'] ?? ''));
            $email = strtolower(trim((string) ($row['email'] ?? '')));

            if ($name === '' || $email === '') {
                $skipped++;
                continue;
            }

            $user = User::query()->where('email', $email)->first();

            if (! $user) {
                User::query()->create([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($this->resolveInitialPasswordForPiket($email)),
                    'role_user_id' => $piketRoleId,
                ]);
                $created++;

                continue;
            }

            $user->update([
                'name' => $name,
                'role_user_id' => $piketRoleId,
            ]);
            $updated++;
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'summary' => $created . ' user dibuat, ' . $updated . ' user diperbarui, ' . $skipped . ' user dilewati',
        ];
    }

    protected function resolveInitialPasswordForPiket(string $email): string
    {
        $localPart = (string) Str::of($email)->before('@');
        $localPart = preg_replace('/[^a-z0-9]/i', '', $localPart);

        if ($localPart === '') {
            return 'piket123';
        }

        return substr(str_pad($localPart, 8, '12345678'), 0, 8);
    }
}
