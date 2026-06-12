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
        'hero_overlay_color' => '#1E3A8A',
        'hero_overlay_opacity' => '80',

        // ---- Konten beranda (diubah via halaman Konten Beranda) ----
        'hero_title' => '', // kosong = "{nama situs} — {tagline}"
        'hero_description' => 'Akses dokumen akademik, kemahasiswaan, penelitian, dan bukti kinerja akreditasi program studi dalam satu pintu.',
        'hero_search_placeholder' => 'Cari judul dokumen…',
        'hero_search_chips' => '["Kurikulum","RPS","Panduan Akademik","Akreditasi","MBKM"]',

        'announcement_enabled' => '0',
        'announcement_text' => '',
        'announcement_link_label' => '',
        'announcement_link_url' => '',

        'login_banner_enabled' => '1',
        'login_banner_text' => 'Mahasiswa atau dosen prodi? Masuk untuk mengakses RPS, modul, dan dokumen internal yang tidak ditampilkan untuk publik.',
        'login_banner_button' => 'Masuk',

        'section_featured_title' => 'Dokumen Unggulan',
        'section_featured_subtitle' => '',
        'section_featured_order' => '1',
        'section_featured_visible' => '1',
        'section_categories_title' => 'Jelajahi Kategori Arsip',
        'section_categories_subtitle' => 'Dokumen tersusun dalam {jumlah} kategori utama program studi.',
        'section_categories_order' => '2',
        'section_categories_visible' => '1',
        'section_popular_title' => 'Paling Banyak Diunduh',
        'section_popular_subtitle' => 'Dokumen yang paling sering dicari pengunjung lain.',
        'section_popular_order' => '3',
        'section_popular_visible' => '1',
        'section_latest_title' => 'Dokumen Terbaru',
        'section_latest_subtitle' => '',
        'section_latest_order' => '4',
        'section_latest_visible' => '1',

        'nav_beranda_order' => '1',
        'nav_arsip_order' => '2',
        'nav_cari_order' => '3',

        'footer_contact_line1' => 'Kampus UNM Gunung Sari',
        'footer_contact_line2' => 'Jl. A. P. Pettarani, Makassar, Sulawesi Selatan',
        'footer_link_label' => 'feb.unm.ac.id',
        'footer_link_url' => 'https://feb.unm.ac.id',
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
