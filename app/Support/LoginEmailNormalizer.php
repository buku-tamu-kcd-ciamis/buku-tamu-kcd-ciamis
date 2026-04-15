<?php

namespace App\Support;

use Illuminate\Support\Str;

class LoginEmailNormalizer
{
    public const INTERNAL_DOMAIN = 'cadisdik13.local';

    /**
     * Segmen gelar/julukan yang umum muncul pada nama pegawai.
     */
    private const TITLE_SEGMENTS = [
        'dr',
        'dra',
        'drs',
        'ir',
        'h',
        'hj',
        'kh',
        'prof',
        'apt',
        's',
        'm',
        'd',
        'a',
        'pd',
        'sos',
        'kom',
        'si',
        'se',
        'sh',
        'st',
        'sp',
        'mm',
        'ma',
        'mt',
        'ti',
        'psi',
        'kes',
        'ag',
        'hum',
        'lc',
        'phd',
        'msc',
        'mba',
        'ak',
    ];

    public static function normalizeEmail(?string $email): string
    {
        return strtolower(trim((string) $email));
    }

    public static function normalizeNameForLogin(?string $name): string
    {
        $raw = trim((string) preg_replace('/\s+/', ' ', str_replace([',', ';'], ' ', (string) $name)));

        if ($raw === '') {
            return '';
        }

        $tokens = array_values(array_filter(
            preg_split('/\s+/', $raw) ?: [],
            fn(string $token): bool => $token !== ''
        ));

        while ($tokens !== [] && self::isTitleToken($tokens[0])) {
            array_shift($tokens);
        }

        while ($tokens !== [] && self::isTitleToken((string) end($tokens))) {
            array_pop($tokens);
        }

        $normalized = trim(implode(' ', $tokens));

        return $normalized !== '' ? $normalized : $raw;
    }

    public static function localPartFromName(?string $name, string $fallback = 'user'): string
    {
        $normalizedName = self::normalizeNameForLogin($name);
        $slug = Str::slug($normalizedName, '.');

        return $slug !== '' ? $slug : $fallback;
    }

    public static function sanitizePreferredEmail(
        ?string $preferredEmail,
        ?string $name,
        string $fallback = 'user',
        string $defaultDomain = self::INTERNAL_DOMAIN
    ): string {
        $normalized = self::normalizeEmail($preferredEmail);

        if ($normalized === '' || ! filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
            return self::localPartFromName($name, $fallback) . '@' . $defaultDomain;
        }

        [$localPart, $domain] = array_pad(explode('@', $normalized, 2), 2, $defaultDomain);
        $localPart = trim((string) $localPart);
        $domain = trim((string) $domain);

        $localPart = $localPart !== '' ? $localPart : $fallback;
        $domain = $domain !== '' ? $domain : $defaultDomain;

        if (strtolower($domain) !== strtolower($defaultDomain)) {
            return $localPart . '@' . $domain;
        }

        $sanitizedLocalPart = self::sanitizeInternalLocalPart($localPart, $name, $fallback);

        return $sanitizedLocalPart . '@' . $domain;
    }

    public static function sanitizeInternalLocalPart(string $localPart, ?string $name, string $fallback = 'user'): string
    {
        $normalizedLocal = strtolower(trim($localPart));
        $normalizedLocal = (string) preg_replace('/[^a-z0-9.]+/', '.', $normalizedLocal);
        $normalizedLocal = trim((string) preg_replace('/\.{2,}/', '.', $normalizedLocal), '.');

        if ($normalizedLocal === '') {
            $normalizedLocal = $fallback;
        }

        $originalSlug = Str::slug((string) $name, '.');
        $cleanSlug = self::localPartFromName($name, $fallback);

        if ($originalSlug !== '' && $cleanSlug !== '' && $normalizedLocal === $originalSlug && $cleanSlug !== $originalSlug) {
            return $cleanSlug;
        }

        $trimmed = self::trimTitleSegmentsFromLocalPart($normalizedLocal);

        return $trimmed !== '' ? $trimmed : $normalizedLocal;
    }

    private static function trimTitleSegmentsFromLocalPart(string $localPart): string
    {
        $segments = array_values(array_filter(
            array_map(static fn(string $segment): string => trim($segment), explode('.', strtolower($localPart))),
            static fn(string $segment): bool => $segment !== ''
        ));

        while (count($segments) > 1) {
            $tail = (string) end($segments);

            if (! in_array($tail, self::TITLE_SEGMENTS, true)) {
                break;
            }

            array_pop($segments);
        }

        return implode('.', $segments);
    }

    private static function isTitleToken(string $token): bool
    {
        $token = strtolower(trim($token, " \t\n\r\0\x0B.,"));

        if ($token === '') {
            return false;
        }

        $parts = array_values(array_filter(
            preg_split('/[.\-\/]+/', $token) ?: [],
            fn(string $part): bool => $part !== ''
        ));

        if (count($parts) > 1) {
            foreach ($parts as $part) {
                $letters = (string) preg_replace('/[^a-z]/', '', $part);

                if ($letters === '' || ! in_array($letters, self::TITLE_SEGMENTS, true)) {
                    return false;
                }
            }

            return true;
        }

        $letters = (string) preg_replace('/[^a-z]/', '', $token);

        return $letters !== '' && in_array($letters, self::TITLE_SEGMENTS, true);
    }
}
