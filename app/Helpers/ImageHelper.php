<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Throwable;

class ImageHelper
{
    /**
     * Proses dan simpan gambar Base64 ke filesystem.
     *
     * @param string|null $base64Data Data Base64 dari frontend (bisa dengan atau tanpa prefix data:image/...)
     * @param string $folder Subfolder di dalam disk 'public', contoh: 'buku-tamu/selfie'
     * @param int $maxWidth Maksimum lebar gambar (pixel)
     * @param int $quality Kualitas kompresi JPEG (1-100)
     * @return string|null Path relatif file yang tersimpan (untuk disimpan di database)
     */
    public static function processAndStore(
        ?string $base64Data,
        string $folder = 'buku-tamu',
        int $maxWidth = 1000,
        int $quality = 65
    ): ?string {
        if (empty($base64Data)) {
            return null;
        }

        // Deteksi apakah ini data:image/png;base64,... atau data:image/jpeg;base64,...
        $isSignature = false;
        $declaredExtension = null;
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $matches)) {
            $declaredExtension = strtolower($matches[1]);
            // Jika PNG (biasanya tanda tangan), simpan sebagai PNG agar transparan tetap terjaga
            if ($declaredExtension === 'png') {
                $isSignature = true;
            }
            // Hapus prefix data URI
            $base64Data = preg_replace('/^data:image\/\w+;base64,/', '', $base64Data);
        }

        $imageData = base64_decode($base64Data);
        if ($imageData === false) {
            return null;
        }

        try {
            // Jalur utama: kompres/resize via Intervention Image.
            $image = Image::read($imageData);

            $currentWidth = $image->width();
            if ($currentWidth > $maxWidth) {
                $image->scaleDown(width: $maxWidth);
            }

            if ($isSignature) {
                $encoded = $image->encode(new PngEncoder());
                $ext = 'png';
            } else {
                $encoded = $image->encode(new JpegEncoder(quality: $quality));
                $ext = 'jpg';
            }

            $filename = Str::uuid() . '.' . $ext;
            $path = $folder . '/' . $filename;
            Storage::disk('public')->put($path, (string) $encoded);

            return $path;
        } catch (Throwable $e) {
            // Fallback ketika GD/Imagick tidak tersedia: simpan biner asli agar submit tidak gagal total.
            Log::warning('Image processing fallback digunakan', ['error' => $e->getMessage()]);

            $ext = self::detectExtension($imageData, $declaredExtension, $isSignature);
            $filename = Str::uuid() . '.' . $ext;
            $path = $folder . '/' . $filename;

            Storage::disk('public')->put($path, $imageData);

            return $path;
        }
    }

    private static function detectExtension(string $imageData, ?string $declaredExtension, bool $isSignature): string
    {
        $declaredExtension = strtolower((string) $declaredExtension);

        if (in_array($declaredExtension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return $declaredExtension === 'jpeg' ? 'jpg' : $declaredExtension;
        }

        if (str_starts_with($imageData, "\x89PNG\r\n\x1A\n")) {
            return 'png';
        }

        if (str_starts_with($imageData, "\xFF\xD8\xFF")) {
            return 'jpg';
        }

        if (substr($imageData, 0, 4) === 'RIFF' && substr($imageData, 8, 4) === 'WEBP') {
            return 'webp';
        }

        return $isSignature ? 'png' : 'jpg';
    }

    /**
     * Resolve URL gambar dari nilai database.
     * Menggunakan path relatif (/storage/...) agar tidak bergantung pada APP_URL.
     * Browser akan me-resolve path ini relatif terhadap domain yang sedang diakses.
     */
    public static function resolveUrl(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // Data URI (Base64) → kembalikan langsung
        if (Str::startsWith($value, 'data:image/')) {
            return $value;
        }

        // URL absolut → kembalikan langsung
        if (Str::startsWith($value, 'http://') || Str::startsWith($value, 'https://')) {
            return $value;
        }

        // Sudah berupa /storage/... → kembalikan langsung
        if (Str::startsWith($value, '/storage/')) {
            return $value;
        }

        // Path file mentah (buku-tamu/xxx.jpg) → tambahkan prefix /storage/
        return '/storage/' . ltrim($value, '/');
    }
}
