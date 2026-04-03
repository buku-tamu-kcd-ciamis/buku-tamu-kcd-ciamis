<?php

namespace App\Repositories;

use App\Models\BukuTamu;
use App\Repositories\Contracts\GuestLookupRepositoryInterface;
use Illuminate\Support\Collection;

class GuestLookupRepository implements GuestLookupRepositoryInterface
{
    public function findCandidates(array $criteria, int $limit = 40): Collection
    {
        $nik = trim((string) ($criteria['nik'] ?? ''));
        $namaLengkap = trim((string) ($criteria['nama_lengkap'] ?? ''));
        $nomorHp = trim((string) ($criteria['nomor_hp'] ?? ''));
        $email = trim((string) ($criteria['email'] ?? ''));

        if ($nik === '' && $namaLengkap === '' && $nomorHp === '' && $email === '') {
            return collect();
        }

        $nomorHpNormalized = $this->normalizePhone($nomorHp);
        $nomorHpLocal = str_starts_with($nomorHpNormalized, '62')
            ? substr($nomorHpNormalized, 2)
            : ltrim($nomorHpNormalized, '0');

        $nomorHpDbExpr = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(nomor_hp, '-', ''), ' ', ''), '+', ''), '(', ''), ')', ''), '.', '')";
        $normalizedEmail = strtolower($email);

        return BukuTamu::query()
            ->select([
                'id',
                'jenis_id',
                'nik',
                'nama_lengkap',
                'instansi',
                'nomor_hp',
                'jabatan',
                'kabupaten_kota',
                'email',
                'created_at',
            ])
            ->where(function ($query) use ($nik, $namaLengkap, $nomorHp, $email, $normalizedEmail, $nomorHpNormalized, $nomorHpLocal, $nomorHpDbExpr) {
                $hasCondition = false;

                if ($nik !== '') {
                    $query->where('nik', $nik);
                    $hasCondition = true;
                }

                if ($namaLengkap !== '') {
                    $method = $hasCondition ? 'orWhere' : 'where';
                    $query->{$method}('nama_lengkap', 'like', '%' . $namaLengkap . '%');
                    $hasCondition = true;
                }

                if ($email !== '') {
                    $method = $hasCondition ? 'orWhereRaw' : 'whereRaw';
                    $query->{$method}('LOWER(email) = ?', [$normalizedEmail]);
                    $hasCondition = true;

                    $method = $hasCondition ? 'orWhere' : 'where';
                    $query->{$method}('email', 'like', '%' . $email . '%');
                    $hasCondition = true;
                }

                if ($nomorHp !== '') {
                    $method = $hasCondition ? 'orWhere' : 'where';
                    $query->{$method}('nomor_hp', 'like', '%' . $nomorHp . '%');
                    $hasCondition = true;
                }

                if ($nomorHpNormalized !== '') {
                    $method = $hasCondition ? 'orWhereRaw' : 'whereRaw';
                    $query->{$method}($nomorHpDbExpr . ' like ?', ['%' . $nomorHpNormalized . '%']);
                    $hasCondition = true;
                }

                if ($nomorHpLocal !== '' && $nomorHpLocal !== $nomorHpNormalized) {
                    $method = $hasCondition ? 'orWhereRaw' : 'whereRaw';
                    $query->{$method}($nomorHpDbExpr . ' like ?', ['%' . $nomorHpLocal . '%']);
                }
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    private function normalizePhone(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }
}
