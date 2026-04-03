<?php

namespace App\Services;

use App\Models\BukuTamu;
use App\Repositories\Contracts\GuestLookupRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class GuestLookupService
{
    public function __construct(
        private readonly GuestLookupRepositoryInterface $guestLookupRepository,
    ) {
    }

    /**
     * @param array{nik?: string, nama_lengkap?: string, nomor_hp?: string, email?: string, suggest?: bool} $payload
     */
    public function lookup(array $payload): array
    {
        $nik = trim((string) ($payload['nik'] ?? ''));
        $namaLengkap = trim((string) ($payload['nama_lengkap'] ?? ''));
        $nomorHp = trim((string) ($payload['nomor_hp'] ?? ''));
        $email = trim((string) ($payload['email'] ?? ''));
        $isSuggest = (bool) ($payload['suggest'] ?? false);

        if ($nik === '' && $namaLengkap === '' && $nomorHp === '' && $email === '') {
            return ['found' => false, 'suggestions' => []];
        }

        $cachePayload = [
            'nik' => $nik,
            'nama_lengkap' => $namaLengkap,
            'nomor_hp' => $nomorHp,
            'email' => strtolower($email),
            'suggest' => $isSuggest,
        ];

        return Cache::remember(
            $this->cacheKey($cachePayload),
            now()->addSeconds(45),
            fn(): array => $this->resolveLookup($nik, $namaLengkap, $nomorHp, $email, $isSuggest)
        );
    }

    private function resolveLookup(
        string $nik,
        string $namaLengkap,
        string $nomorHp,
        string $email,
        bool $isSuggest
    ): array {
        if ($isSuggest) {
            if (!$this->hasMeaningfulSuggestionInput($nik, $namaLengkap, $nomorHp, $email)) {
                return ['found' => false, 'suggestions' => []];
            }

            $candidates = $this->guestLookupRepository->findCandidates([
                'nama_lengkap' => $namaLengkap,
                'nomor_hp' => $nomorHp,
                'email' => $email,
            ], 50);

            return [
                'found' => false,
                'suggestions' => $this->buildSuggestionPayload($candidates),
            ];
        }

        $candidates = $this->guestLookupRepository->findCandidates([
            'nik' => $nik,
            'nama_lengkap' => $namaLengkap,
            'nomor_hp' => $nomorHp,
            'email' => $email,
        ]);

        if ($nik !== '') {
            $guest = $candidates->first(fn(BukuTamu $candidate): bool => (string) ($candidate->nik ?? '') === $nik);

            if ($guest) {
                return [
                    'found' => true,
                    'matched_by' => 'nik',
                    'data' => $this->formatGuestData($guest),
                ];
            }
        }

        if ($email !== '') {
            $normalizedEmail = strtolower($email);

            $guest = $candidates->first(
                fn(BukuTamu $candidate): bool => strtolower((string) ($candidate->email ?? '')) === $normalizedEmail
            );

            if (!$guest) {
                $guest = $candidates->first(
                    fn(BukuTamu $candidate): bool => str_contains(strtolower((string) ($candidate->email ?? '')), $normalizedEmail)
                );
            }

            if ($guest) {
                return [
                    'found' => true,
                    'matched_by' => 'email',
                    'data' => $this->formatGuestData($guest),
                ];
            }
        }

        if ($namaLengkap !== '') {
            $normalizedName = strtolower($namaLengkap);

            $nameMatches = $candidates
                ->filter(fn(BukuTamu $candidate): bool => str_contains(strtolower((string) ($candidate->nama_lengkap ?? '')), $normalizedName))
                ->values();

            if ($nameMatches->count() > 1) {
                $nameSuggestions = $this->buildSuggestionPayload($nameMatches);

                if ($nameSuggestions->count() === 1) {
                    return [
                        'found' => true,
                        'matched_by' => 'nama_lengkap',
                        'data' => $nameSuggestions->first(),
                    ];
                }

                return [
                    'found' => false,
                    'multiple' => true,
                    'matched_by' => 'nama_lengkap',
                    'suggestions' => $nameSuggestions,
                ];
            }

            $guest = $nameMatches->first();

            if ($guest) {
                return [
                    'found' => true,
                    'matched_by' => 'nama_lengkap',
                    'data' => $this->formatGuestData($guest),
                ];
            }
        }

        if ($nomorHp !== '') {
            $nomorHpNormalized = $this->normalizePhone($nomorHp);

            $guest = $candidates->first(
                fn(BukuTamu $candidate): bool => $this->phoneMatchesInput((string) ($candidate->nomor_hp ?? ''), $nomorHp, $nomorHpNormalized)
            );

            if ($guest) {
                return [
                    'found' => true,
                    'matched_by' => 'nomor_hp',
                    'data' => $this->formatGuestData($guest),
                ];
            }
        }

        return [
            'found' => false,
            'suggestions' => $this->buildSuggestionPayload($candidates),
        ];
    }

    private function cacheKey(array $payload): string
    {
        return 'guest_lookup_api:' . sha1(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function hasMeaningfulSuggestionInput(string $nik, string $namaLengkap, string $nomorHp, string $email): bool
    {
        return strlen($nik) >= 4
            || mb_strlen($namaLengkap) >= 2
            || strlen($this->normalizePhone($nomorHp)) >= 4
            || mb_strlen($email) >= 3;
    }

    private function phoneMatchesInput(string $dbPhone, string $rawInput, string $normalizedInput): bool
    {
        if ($rawInput !== '' && str_contains($dbPhone, $rawInput)) {
            return true;
        }

        $dbPhoneNormalized = $this->normalizePhone($dbPhone);

        if ($normalizedInput !== '' && str_contains($dbPhoneNormalized, $normalizedInput)) {
            return true;
        }

        $localInput = str_starts_with($normalizedInput, '62')
            ? substr($normalizedInput, 2)
            : ltrim($normalizedInput, '0');

        return $localInput !== ''
            && $localInput !== $normalizedInput
            && str_contains($dbPhoneNormalized, $localInput);
    }

    private function formatGuestData(BukuTamu $guest, bool $forSuggestion = false): array
    {
        $payload = [
            'jenis_id' => $guest->jenis_id,
            'nik' => $guest->nik,
            'nama_lengkap' => $guest->nama_lengkap,
            'instansi' => $guest->instansi,
            'nomor_hp' => $guest->nomor_hp,
            'jabatan' => $guest->jabatan,
            'kabupaten_kota' => $guest->kabupaten_kota,
            'email' => $guest->email,
        ];

        if ($forSuggestion) {
            $payload['display_label'] = trim(
                ($guest->nama_lengkap ?? '-') .
                    ' | ' .
                    ($guest->jenis_id ?? '-') . ': ' . ($guest->nik ?? '-') .
                    ' | ' .
                    ($guest->nomor_hp ?? '-')
            );
        }

        return $payload;
    }

    private function buildSuggestionPayload($guests)
    {
        return $guests
            ->unique(function (BukuTamu $guest) {
                return strtolower(
                    ($guest->nama_lengkap ?? '') . '|' .
                        ($guest->nik ?? '') . '|' .
                        ($guest->nomor_hp ?? '') . '|' .
                        ($guest->email ?? '')
                );
            })
            ->take(8)
            ->map(fn(BukuTamu $guest) => $this->formatGuestData($guest, true))
            ->values();
    }

    private function normalizePhone(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }
}
