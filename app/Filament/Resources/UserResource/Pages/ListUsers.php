<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Exports\PegawaiTemplateExport;
use App\Exports\UserExport;
use App\Imports\PegawaiImport;
use App\Filament\Resources\UserResource;
use App\Models\Pegawai;
use App\Models\RoleUser;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
                ->color('success')
                ->modalHeading('Import Data Pegawai dari Excel')
                ->modalDescription('Unggah file Excel untuk sinkron data pegawai sekaligus akun user. Role user dibaca dari kolom "Role User" di template import.')
                ->schema([
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
                        ->helperText('Isi kolom "Role User" pada template dengan nilai: Staff, Piket, atau Kepala Cabang Dinas.')
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
                        $syncResult = $this->syncUsersByImportedPegawaiRows($importer->getProcessedRows());

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
                ->color('success')
                ->action(fn() => (new PegawaiTemplateExport())->download()),
            Actions\Action::make('exportPegawaiExcel')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(fn() => (new UserExport())->download()),
            Actions\CreateAction::make()
                ->label('Buat')
                ->color('success'),
        ];
    }

    protected function syncUsersByImportedPegawaiRows(array $rows): array
    {
        if ($rows === []) {
            return [
                'created' => 0,
                'updated' => 0,
                'summary' => '0 user dibuat, 0 user diperbarui, 0 user dilewati',
            ];
        }

        $rowsByNip = [];
        foreach ($rows as $row) {
            $nip = preg_replace('/[^0-9]/', '', (string) ($row['nip'] ?? ''));

            if ($nip === '') {
                continue;
            }

            $rowsByNip[$nip] = [
                'nip' => $nip,
                'role_user_name' => (string) ($row['role_user_name'] ?? ''),
            ];
        }

        if ($rowsByNip === []) {
            return [
                'created' => 0,
                'updated' => 0,
                'summary' => '0 user dibuat, 0 user diperbarui, 0 user dilewati',
            ];
        }

        $pegawais = Pegawai::query()
            ->whereIn('nip', array_keys($rowsByNip))
            ->get(['id', 'nama', 'nip']);

        $roleNameToIdMap = $this->getRoleNameToIdMap();
        $defaultRoleId = $roleNameToIdMap[$this->normalizeRoleName('Staff')] ?? null;
        $kepalaCabdinRoleId = $roleNameToIdMap[$this->normalizeRoleName('Kepala Cabang Dinas')] ?? null;
        $currentKepalaCabdinUserId = null;

        if ($kepalaCabdinRoleId !== null) {
            $currentKepalaCabdinUserId = User::query()
                ->where('role_user_id', $kepalaCabdinRoleId)
                ->value('id');
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $fallbackRoleCount = 0;
        $kepalaCabdinBlockedCount = 0;

        foreach ($pegawais as $pegawai) {
            $row = $rowsByNip[(string) $pegawai->nip] ?? [];
            $requestedRoleName = (string) ($row['role_user_name'] ?? '');
            $resolvedRoleId = $this->resolveRoleIdFromName($requestedRoleName, $roleNameToIdMap);

            if ($resolvedRoleId === null) {
                $resolvedRoleId = $defaultRoleId;
                $fallbackRoleCount++;
            }

            if ($resolvedRoleId === null) {
                $skipped++;
                continue;
            }

            $user = User::query()->where('pegawai_id', $pegawai->id)->first();

            // Kepala Cabang Dinas must stay unique: only one user may hold this role.
            if (
                $kepalaCabdinRoleId !== null
                && (int) $resolvedRoleId === (int) $kepalaCabdinRoleId
                && $currentKepalaCabdinUserId !== null
                && (! $user || (string) $user->id !== (string) $currentKepalaCabdinUserId)
            ) {
                $skipped++;
                $kepalaCabdinBlockedCount++;
                continue;
            }

            if (! $user) {
                $email = $this->generateUniqueEmailFromName($pegawai->nama);

                $newUser = User::query()->create([
                    'name' => (string) $pegawai->nama,
                    'email' => $email,
                    'password' => Hash::make((string) $pegawai->nip),
                    'role_user_id' => $resolvedRoleId,
                    'pegawai_id' => $pegawai->id,
                ]);

                if ($kepalaCabdinRoleId !== null && (int) $resolvedRoleId === (int) $kepalaCabdinRoleId) {
                    $currentKepalaCabdinUserId = $newUser->id;
                }

                $created++;

                continue;
            }

            $user->update([
                'name' => (string) ($pegawai->nama ?: $user->name),
                'role_user_id' => $resolvedRoleId,
            ]);

            if ($kepalaCabdinRoleId !== null && (int) $resolvedRoleId === (int) $kepalaCabdinRoleId) {
                $currentKepalaCabdinUserId = $user->id;
            }

            $updated++;
        }

        $summary = $created . ' user dibuat, ' . $updated . ' user diperbarui, ' . $skipped . ' user dilewati';
        if ($fallbackRoleCount > 0) {
            $summary .= ', ' . $fallbackRoleCount . ' user pakai role default Staff';
        }
        if ($kepalaCabdinBlockedCount > 0) {
            $summary .= ', ' . $kepalaCabdinBlockedCount . ' baris ditolak karena role Kepala Cabang Dinas hanya boleh 1 user';
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'summary' => $summary,
        ];
    }

    protected function getRoleNameToIdMap(): array
    {
        $map = [];

        foreach (RoleUser::query()->where('name', '!=', 'Super Admin')->get(['id', 'name']) as $role) {
            $normalized = $this->normalizeRoleName($role->name);
            if ($normalized !== '') {
                $map[$normalized] = $role->id;
            }
        }

        return $map;
    }

    protected function resolveRoleIdFromName(?string $roleName, array $roleNameToIdMap): ?int
    {
        $normalized = $this->normalizeRoleName($roleName);

        if ($normalized === '') {
            return null;
        }

        if (isset($roleNameToIdMap[$normalized])) {
            return (int) $roleNameToIdMap[$normalized];
        }

        $aliases = [
            'kepala cabdin' => 'kepala cabang dinas',
            'kepala cabang' => 'kepala cabang dinas',
        ];

        $aliasTarget = $aliases[$normalized] ?? null;

        if ($aliasTarget !== null && isset($roleNameToIdMap[$aliasTarget])) {
            return (int) $roleNameToIdMap[$aliasTarget];
        }

        return null;
    }

    protected function normalizeRoleName(?string $roleName): string
    {
        $normalized = strtolower(trim((string) $roleName));
        if ($normalized === '') {
            return '';
        }

        $normalized = preg_replace('/[^a-z0-9]+/i', ' ', $normalized);

        return trim((string) $normalized);
    }

    protected function generateUniqueEmailFromName(?string $name): string
    {
        $baseSlug = Str::slug((string) $name, '.');
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'user';
        $domain = 'cadisdik13.local';
        $counter = 0;

        do {
            $suffix = $counter > 0 ? '.' . $counter : '';
            $candidate = $baseSlug . $suffix . '@' . $domain;
            $exists = User::query()->where('email', $candidate)->exists();
            $counter++;
        } while ($exists);

        return $candidate;
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
