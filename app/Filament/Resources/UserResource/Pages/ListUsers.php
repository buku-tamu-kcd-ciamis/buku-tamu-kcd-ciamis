<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Exports\PegawaiTemplateExport;
use App\Exports\UserExport;
use App\Imports\PegawaiImport;
use App\Filament\Resources\UserResource;
use App\Models\Pegawai;
use App\Models\RoleUser;
use App\Models\User;
use App\Support\LoginEmailNormalizer;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
                ->color('success')
                ->modalHeading('Import Data Pegawai dari Excel')
                ->modalDescription('Unggah file Excel dengan format utama: Nama Pegawai, NIP, Pangkat/Golongan, Jabatan, Unit Kerja. Sinkron akun user akan mengikuti data import.')
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
                        ->helperText('Kolom "Role User (Opsional)" dapat diisi: Staff, Piket, atau Kepala Cabang Dinas. Kolom "Password Login (Opsional)" bisa diisi jika ingin set password tertentu; jika kosong sistem pakai default/generate otomatis.')
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
                        Log::error('Import Data Pegawai (User Resource) gagal.', [
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
        $rowsByEmail = [];

        foreach ($rows as $row) {
            $nip = preg_replace('/[^0-9]/', '', (string) ($row['nip'] ?? ''));
            $email = $this->normalizeEmail($row['email'] ?? null);

            if ($nip === '' && $email === '') {
                continue;
            }

            $payload = [
                'nip' => $nip,
                'email' => $email,
                'role_user_name' => (string) ($row['role_user_name'] ?? ''),
                'initial_password' => trim((string) ($row['initial_password'] ?? '')),
            ];

            if ($nip !== '') {
                $rowsByNip[$nip] = $payload;
            }

            if ($email !== '') {
                $rowsByEmail[$email] = $payload;
            }
        }

        if ($rowsByNip === [] && $rowsByEmail === []) {
            return [
                'created' => 0,
                'updated' => 0,
                'summary' => '0 user dibuat, 0 user diperbarui, 0 user dilewati',
            ];
        }

        $pegawais = Pegawai::query()
            ->where(function (Builder $query) use ($rowsByNip, $rowsByEmail): void {
                if ($rowsByNip !== []) {
                    $query->whereIn('nip', array_keys($rowsByNip));
                }

                if ($rowsByEmail !== []) {
                    $query->orWhereIn('email', array_keys($rowsByEmail));
                }
            })
            ->get(['id', 'nama', 'nip', 'email']);

        $roleNameToIdMap = $this->getRoleNameToIdMap();
        $staffRoleId = $roleNameToIdMap[$this->normalizeRoleName('Staff')] ?? null;
        $piketRoleId = $roleNameToIdMap[$this->normalizeRoleName('Piket')] ?? null;
        $defaultRoleId = $staffRoleId;
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
        $preservedRoleCount = 0;

        foreach ($pegawais as $pegawai) {
            $row = [];
            $pegawaiNip = preg_replace('/[^0-9]/', '', (string) ($pegawai->nip ?? ''));
            $pegawaiEmail = $this->normalizeEmail($pegawai->email);

            if ($pegawaiNip !== '' && isset($rowsByNip[$pegawaiNip])) {
                $row = $rowsByNip[$pegawaiNip];
            } elseif ($pegawaiEmail !== '' && isset($rowsByEmail[$pegawaiEmail])) {
                $row = $rowsByEmail[$pegawaiEmail];
            }

            if ($row === []) {
                $skipped++;
                continue;
            }

            $user = User::query()->where('pegawai_id', $pegawai->id)->first();

            $requestedRoleName = (string) ($row['role_user_name'] ?? '');
            $importedInitialPassword = trim((string) ($row['initial_password'] ?? ''));
            $hasExplicitRole = $this->normalizeRoleName($requestedRoleName) !== '';
            $resolvedRoleId = $hasExplicitRole
                ? $this->resolveRoleIdFromName($requestedRoleName, $roleNameToIdMap)
                : null;

            if ($resolvedRoleId === null) {
                if ($hasExplicitRole) {
                    $resolvedRoleId = $defaultRoleId;
                    if ($resolvedRoleId !== null) {
                        $fallbackRoleCount++;
                    }
                } elseif ($user) {
                    $resolvedRoleId = (int) $user->role_user_id;
                    $preservedRoleCount++;
                } else {
                    $resolvedRoleId = $defaultRoleId;
                    if ($resolvedRoleId !== null) {
                        $fallbackRoleCount++;
                    }
                }
            }

            if ($resolvedRoleId === null) {
                $skipped++;
                continue;
            }

            $preferredEmail = $this->normalizeEmail($row['email'] ?? $pegawai->email);
            $resolvedEmail = $this->resolveUniqueUserEmail(
                $preferredEmail,
                $pegawai->nama,
                $user?->id
            );

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
                $initialPassword = $this->resolveInitialPassword($pegawai, $resolvedEmail);

                if ($piketRoleId !== null && (int) $resolvedRoleId === (int) $piketRoleId) {
                    $initialPassword = 'piket123';
                } elseif ($staffRoleId !== null && (int) $resolvedRoleId === (int) $staffRoleId) {
                    $initialPassword = 'staff123';
                } elseif ($kepalaCabdinRoleId !== null && (int) $resolvedRoleId === (int) $kepalaCabdinRoleId) {
                    $initialPassword = 'kepalakcd123';
                }

                if ($importedInitialPassword !== '') {
                    $initialPassword = $importedInitialPassword;
                }

                $newUser = User::query()->create([
                    'name' => (string) $pegawai->nama,
                    'email' => $resolvedEmail,
                    'password' => Hash::make($initialPassword),
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
                'email' => $resolvedEmail,
                'role_user_id' => $resolvedRoleId,
            ]);

            if ($importedInitialPassword !== '') {
                $user->update([
                    'password' => Hash::make($importedInitialPassword),
                ]);
            }

            if ($kepalaCabdinRoleId !== null && (int) $resolvedRoleId === (int) $kepalaCabdinRoleId) {
                $currentKepalaCabdinUserId = $user->id;
            }

            $updated++;
        }

        $summary = $created . ' user dibuat, ' . $updated . ' user diperbarui, ' . $skipped . ' user dilewati';
        if ($fallbackRoleCount > 0) {
            $summary .= ', ' . $fallbackRoleCount . ' user pakai role default Staff';
        }
        if ($preservedRoleCount > 0) {
            $summary .= ', ' . $preservedRoleCount . ' user mempertahankan role lama (kolom role kosong)';
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

    protected function normalizeEmail(?string $email): string
    {
        return LoginEmailNormalizer::normalizeEmail($email);
    }

    protected function resolveUniqueUserEmail(?string $preferredEmail, ?string $name, ?int $ignoreUserId = null): string
    {
        $normalized = LoginEmailNormalizer::sanitizePreferredEmail($preferredEmail, $name, 'user');

        if ($normalized === '' || ! filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
            $normalized = $this->generateUniqueEmailFromName($name, $ignoreUserId);

            return $normalized;
        }

        [$localPart, $domain] = array_pad(explode('@', $normalized, 2), 2, 'cadisdik13.local');
        $localPart = $localPart !== '' ? $localPart : 'user';
        $domain = $domain !== '' ? $domain : 'cadisdik13.local';

        $counter = 0;
        do {
            $suffix = $counter > 0 ? '.' . $counter : '';
            $candidate = $localPart . $suffix . '@' . $domain;
            $exists = User::query()
                ->when($ignoreUserId !== null, fn($query) => $query->where('id', '!=', $ignoreUserId))
                ->where('email', $candidate)
                ->exists();
            $counter++;
        } while ($exists);

        return $candidate;
    }

    protected function generateUniqueEmailFromName(?string $name, ?int $ignoreUserId = null): string
    {
        $baseSlug = LoginEmailNormalizer::localPartFromName($name, 'user');
        $domain = 'cadisdik13.local';
        $counter = 0;

        do {
            $suffix = $counter > 0 ? '.' . $counter : '';
            $candidate = $baseSlug . $suffix . '@' . $domain;
            $exists = User::query()
                ->when($ignoreUserId !== null, fn($query) => $query->where('id', '!=', $ignoreUserId))
                ->where('email', $candidate)
                ->exists();
            $counter++;
        } while ($exists);

        return $candidate;
    }

    protected function resolveInitialPassword(Pegawai $pegawai, string $resolvedEmail): string
    {
        $nip = preg_replace('/[^0-9]/', '', (string) ($pegawai->nip ?? ''));
        if ($nip !== '') {
            return $nip;
        }

        $localPart = explode('@', $resolvedEmail)[0] ?? '';
        $localPart = preg_replace('/[^a-z0-9]/i', '', (string) $localPart);
        if ($localPart === '') {
            return 'pegawai123';
        }

        return substr(str_pad($localPart, 8, '12345678'), 0, 8);
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
