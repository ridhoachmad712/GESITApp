<?php

namespace App\Support;

class ColorPalette
{
    /**
     * Persentase campuran per gradasi: positif = ke arah putih,
     * negatif = ke arah hitam. 500 = warna dasar pilihan admin.
     */
    private const MIXES = [
        50 => 0.93,
        100 => 0.85,
        200 => 0.70,
        300 => 0.50,
        400 => 0.26,
        500 => 0.0,
        600 => -0.14,
        700 => -0.30,
        800 => -0.46,
        900 => -0.62,
    ];

    /**
     * Hasilkan 10 gradasi dari satu warna hex, sebagai triplet "R G B"
     * (format yang dibutuhkan CSS variable Tailwind dengan <alpha-value>).
     *
     * @return array<int, string>
     */
    public static function shades(string $hex): array
    {
        [$r, $g, $b] = self::hexToRgb($hex);

        $shades = [];

        foreach (self::MIXES as $shade => $mix) {
            $target = $mix >= 0 ? 255 : 0;
            $ratio = abs($mix);

            $shades[$shade] = sprintf(
                '%d %d %d',
                (int) round($r + ($target - $r) * $ratio),
                (int) round($g + ($target - $g) * $ratio),
                (int) round($b + ($target - $b) * $ratio),
            );
        }

        return $shades;
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    public static function hexToRgb(string $hex): array
    {
        $hex = ltrim(trim($hex), '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (! preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            $hex = '1E3A8A'; // fallback navy bila nilai tidak valid
        }

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }
}
