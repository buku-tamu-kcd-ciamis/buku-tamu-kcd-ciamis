<?php

namespace App\Support;

class PrintPaperSize
{
    /**
     * @var array<string, array{label: string, width_mm: int, height_mm: int}>
     */
    private const SIZES = [
        'a3' => ['label' => 'A3 (297 x 420 mm)', 'width_mm' => 297, 'height_mm' => 420],
        'a4' => ['label' => 'A4 (210 x 297 mm)', 'width_mm' => 210, 'height_mm' => 297],
        'a5' => ['label' => 'A5 (148 x 210 mm)', 'width_mm' => 148, 'height_mm' => 210],
        'b4' => ['label' => 'B4 (250 x 353 mm)', 'width_mm' => 250, 'height_mm' => 353],
        'b5' => ['label' => 'B5 (176 x 250 mm)', 'width_mm' => 176, 'height_mm' => 250],
        'letter' => ['label' => 'Letter (216 x 279 mm)', 'width_mm' => 216, 'height_mm' => 279],
        'legal' => ['label' => 'Legal (216 x 356 mm)', 'width_mm' => 216, 'height_mm' => 356],
        'f4' => ['label' => 'F4 (215 x 330 mm)', 'width_mm' => 215, 'height_mm' => 330],
    ];

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::SIZES as $key => $size) {
            $options[$key] = $size['label'];
        }

        return $options;
    }

    public static function normalize(?string $paperSize): string
    {
        $key = strtolower(trim((string) $paperSize));

        if ($key === '' || ! array_key_exists($key, self::SIZES)) {
            return 'a4';
        }

        return $key;
    }

    public static function pageSize(string $paperSize, string $orientation = 'portrait'): string
    {
        [$width, $height] = self::dimensions($paperSize, $orientation);

        return $width . 'mm ' . $height . 'mm';
    }

    public static function pageWidth(string $paperSize, string $orientation = 'portrait'): string
    {
        [$width] = self::dimensions($paperSize, $orientation);

        return $width . 'mm';
    }

    /**
     * @return array{0: int, 1: int}
     */
    private static function dimensions(string $paperSize, string $orientation): array
    {
        $normalized = self::normalize($paperSize);
        $size = self::SIZES[$normalized];

        $width = $size['width_mm'];
        $height = $size['height_mm'];

        if (strtolower($orientation) === 'landscape') {
            [$width, $height] = [$height, $width];
        }

        return [$width, $height];
    }
}
