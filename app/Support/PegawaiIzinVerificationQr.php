<?php

namespace App\Support;

use App\Models\PegawaiIzin;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\URL;

class PegawaiIzinVerificationQr
{
    public static function signedPreviewUrl(PegawaiIzin $pegawai): string
    {
        return URL::signedRoute('pegawai-izin.preview', [
            'id' => $pegawai->id,
        ]);
    }

    public static function signedPreviewQrDataUri(PegawaiIzin $pegawai, int $size = 140): ?string
    {
        try {
            $qrCode = new QrCode(
                data: static::signedPreviewUrl($pegawai),
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::Medium,
                size: max(80, $size),
                margin: 8,
            );

            $result = (new PngWriter())->write($qrCode);

            return $result->getDataUri();
        } catch (\Throwable) {
            return null;
        }
    }
}
