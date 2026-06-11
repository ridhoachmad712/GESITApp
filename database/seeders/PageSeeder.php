<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Halaman statis profil prodi. Konten awal adalah placeholder —
     * admin mengeditnya melalui panel Filament (menu Halaman Statis).
     */
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'sejarah',
                'title' => 'Sejarah Program Studi',
                'content' => '<p>Program Studi Manajemen Fakultas Ekonomi dan Bisnis Universitas Negeri Makassar '
                    .'didirikan untuk menyiapkan sumber daya manusia yang unggul di bidang manajemen. '
                    .'<em>(Konten ini adalah placeholder — silakan perbarui melalui panel admin.)</em></p>',
            ],
            [
                'slug' => 'visi-misi',
                'title' => 'Visi, Misi, Tujuan, dan Strategi',
                'content' => '<h2>Visi</h2><p>Menjadi program studi yang unggul dalam pengembangan ilmu manajemen. '
                    .'<em>(Placeholder — perbarui melalui panel admin.)</em></p>'
                    .'<h2>Misi</h2><p>Menyelenggarakan pendidikan, penelitian, dan pengabdian masyarakat '
                    .'di bidang manajemen yang bermutu.</p>',
            ],
            [
                'slug' => 'struktur-organisasi',
                'title' => 'Struktur Organisasi',
                'content' => '<p>Struktur organisasi Program Studi Manajemen FEB UNM. '
                    .'<em>(Placeholder — perbarui melalui panel admin, bisa menyertakan bagan.)</em></p>',
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(['slug' => $page['slug']], $page);
        }
    }
}
