<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    public const CACHE_KEY = 'app-settings';

    /**
     * Nilai bawaan — dipakai bila baris belum ada di database.
     */
    public const DEFAULTS = [
        'site_name' => 'GESIT',
        'site_tagline' => 'Gerakan Sistem Informasi Terpadu',
        'site_owner' => 'Program Studi Manajemen FEB Universitas Negeri Makassar',
        'primary_color' => '#1E3A8A',
    ];

    protected $fillable = ['key', 'value'];

    public static function get(string $key, ?string $default = null): ?string
    {
        $default ??= self::DEFAULTS[$key] ?? null;

        try {
            $all = Cache::rememberForever(self::CACHE_KEY, fn (): array => static::query()
                ->pluck('value', 'key')
                ->all());
        } catch (\Throwable) {
            // Tabel belum ada (mis. saat migrasi awal/console) — pakai default
            return $default;
        }

        return $all[$key] ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);

        Cache::forget(self::CACHE_KEY);
    }
}
