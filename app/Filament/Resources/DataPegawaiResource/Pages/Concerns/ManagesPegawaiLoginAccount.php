<?php

namespace App\Filament\Resources\DataPegawaiResource\Pages\Concerns;

use App\Models\Pegawai;
use App\Models\RoleUser;
use App\Models\User;
use App\Support\LoginEmailNormalizer;
use Illuminate\Support\Facades\Hash;

trait ManagesPegawaiLoginAccount
{
    protected function pullLoginPasswordFromFormData(array &$data): string
    {
        $password = trim((string) ($data['login_password'] ?? ''));

        unset($data['login_password'], $data['login_password_confirmation']);

        return $password;
    }

    protected function syncLoginAccountPasswordForPegawai(Pegawai $pegawai, string $plainPassword): array
    {
        $user = User::query()
            ->where('pegawai_id', $pegawai->id)
            ->first();

        if (! $user && filled($pegawai->email)) {
            $user = User::query()
                ->where('email', $pegawai->email)
                ->first();
        }

        if (! $user) {
            $roleId = $this->resolveRoleIdFromJabatan($pegawai->jabatan);

            if (! $roleId) {
                return [
                    'updated' => false,
                    'created' => false,
                    'message' => 'Akun login tidak dapat dibuat karena role default tidak ditemukan.',
                ];
            }

            $resolvedEmail = $this->resolveUniqueUserEmail($pegawai->email, $pegawai->nama);

            $user = User::query()->create([
                'name' => (string) $pegawai->nama,
                'email' => $resolvedEmail,
                'password' => Hash::make($plainPassword),
                'role_user_id' => $roleId,
                'pegawai_id' => $pegawai->id,
            ]);

            return [
                'updated' => true,
                'created' => true,
                'message' => 'Akun login berhasil dibuat dan password berhasil disimpan.',
            ];
        }

        $user->update([
            'password' => Hash::make($plainPassword),
            'pegawai_id' => $pegawai->id,
            'name' => (string) ($pegawai->nama ?: $user->name),
        ]);

        return [
            'updated' => true,
            'created' => false,
            'message' => 'Password login berhasil diperbarui.',
        ];
    }

    protected function resolveRoleIdFromJabatan(?string $jabatan): ?int
    {
        $normalized = strtolower(trim((string) $jabatan));

        if ($normalized !== '' && str_contains($normalized, 'kepala cabang')) {
            return RoleUser::query()->where('name', 'Kepala Cabang Dinas')->value('id');
        }

        if ($normalized !== '' && str_contains($normalized, 'piket')) {
            return RoleUser::query()->where('name', 'Piket')->value('id');
        }

        return RoleUser::query()->where('name', 'Staff')->value('id');
    }

    protected function resolveUniqueUserEmail(?string $preferredEmail, ?string $name, ?string $ignoreUserId = null): string
    {
        $normalized = LoginEmailNormalizer::sanitizePreferredEmail($preferredEmail, $name, 'user');

        if ($normalized === '' || ! filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
            return $this->generateUniqueEmailFromName($name, $ignoreUserId);
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

    protected function generateUniqueEmailFromName(?string $name, ?string $ignoreUserId = null): string
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
}
