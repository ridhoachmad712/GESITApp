<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * 9 kategori utama prodi beserta sub-kategorinya,
     * sesuai PLAN-SIARSIP.md bagian 3.2.
     */
    public function run(): void
    {
        $structure = [
            [
                'name' => 'Profil dan Dokumen Dasar',
                'icon' => 'heroicon-o-building-library',
                'description' => 'Identitas, dasar hukum, dan pedoman dasar program studi.',
                'children' => [
                    'Sejarah Program Studi',
                    'Visi, Misi, Tujuan, dan Strategi',
                    'Struktur Organisasi',
                    'Rencana Strategis (Renstra)',
                    'SOP dan Pedoman Akademik',
                    'Buku Evaluasi Penyelesaian Studi',
                    'Buku Panduan Akademik',
                ],
            ],
            [
                'name' => 'Arsip Akademik',
                'icon' => 'heroicon-o-academic-cap',
                'description' => 'Kurikulum, perangkat pembelajaran, dan evaluasi akademik.',
                'children' => [
                    'Kurikulum dan Peta Kurikulum',
                    'Capaian Pembelajaran Lulusan (CPL)',
                    'Rencana Pembelajaran Semester (RPS)',
                    'Kontrak Kuliah',
                    'Modul Pembelajaran dan Bahan Ajar',
                    'E-Book',
                    'Absensi Perkuliahan',
                    'Kisi-Kisi UTS/UAS',
                    'Rubrik Penilaian dan Instrumen Evaluasi',
                    'Rekap Hasil Evaluasi',
                ],
            ],
            [
                'name' => 'Arsip Kemahasiswaan',
                'icon' => 'heroicon-o-user-group',
                'description' => 'Pedoman, prestasi, dan kegiatan kemahasiswaan.',
                'children' => [
                    'Buku Pedoman Kemahasiswaan',
                    'Panduan MBKM',
                    'Prestasi Mahasiswa',
                    'Dokumentasi Kegiatan Kemahasiswaan',
                    'Program Kreativitas Mahasiswa (PKM)',
                    'Kegiatan Organisasi Mahasiswa (Ormawa)',
                    'Alumni dan Tracer Study',
                ],
            ],
            [
                'name' => 'Arsip Dosen',
                'icon' => 'heroicon-o-identification',
                'description' => 'Profil, kompetensi, dan karya dosen program studi.',
                'children' => [
                    'Profil Dosen',
                    'CV Dosen',
                    'Sertifikat Kompetensi',
                    'BKD dan Beban Mengajar',
                    'Publikasi Ilmiah',
                    'Buku Ajar',
                    'Hak Kekayaan Intelektual (HKI)',
                    'Pengabdian Masyarakat',
                ],
            ],
            [
                'name' => 'Arsip Penelitian dan Pengabdian',
                'icon' => 'heroicon-o-beaker',
                'description' => 'Roadmap, proposal, laporan, dan luaran penelitian serta pengabdian.',
                'children' => [
                    'Roadmap Penelitian dan Pengabdian',
                    'Proposal Penelitian dan Pengabdian',
                    'Laporan Penelitian dan Pengabdian',
                    'Artikel Jurnal dan Prosiding',
                    'Dokumentasi Kegiatan Penelitian dan Pengabdian',
                ],
            ],
            [
                'name' => 'Arsip Kerja Sama',
                'icon' => 'heroicon-o-hand-raised',
                'description' => 'Dokumen MoU/MoA/IA dan implementasi kerja sama.',
                'children' => [
                    'Daftar MoU/MoA/IA',
                    'File MoU/MoA/IA',
                    'Laporan Implementasi dan Evaluasi Kerja Sama',
                    'Dokumentasi Kegiatan Kerja Sama',
                ],
            ],
            [
                'name' => 'Arsip Penjaminan Mutu',
                'icon' => 'heroicon-o-shield-check',
                'description' => 'Dokumen SPMI, audit mutu internal, dan survei kepuasan.',
                'children' => [
                    'Dokumen SPMI, Manual, dan Standar Mutu',
                    'Formulir Mutu',
                    'Audit Mutu Internal (AMI) dan Tindak Lanjut',
                    'Survei Kepuasan',
                ],
            ],
            [
                'name' => 'Arsip Akreditasi',
                'icon' => 'heroicon-o-check-badge',
                'description' => 'Sertifikat akreditasi dan dokumen bukti kinerja LAMEMBA.',
                'children' => [
                    'Sertifikat Akreditasi',
                    'Laporan Kinerja Program Studi (LKPS)',
                    'Laporan Evaluasi Diri (LED)',
                    'Dokumen Pendukung dan Bukti Kinerja',
                ],
            ],
            [
                'name' => 'Dokumentasi',
                'icon' => 'heroicon-o-photo',
                'description' => 'Galeri foto dan video kegiatan program studi.',
                'children' => [],
            ],
        ];

        foreach ($structure as $i => $main) {
            $parent = Category::updateOrCreate(
                ['slug' => Str::slug($main['name'])],
                [
                    'name' => $main['name'],
                    'icon' => $main['icon'],
                    'description' => $main['description'],
                    'parent_id' => null,
                    'sort_order' => $i + 1,
                ],
            );

            foreach ($main['children'] as $j => $childName) {
                Category::updateOrCreate(
                    ['slug' => Str::slug($childName)],
                    [
                        'name' => $childName,
                        'parent_id' => $parent->id,
                        'sort_order' => $j + 1,
                    ],
                );
            }
        }
    }
}
