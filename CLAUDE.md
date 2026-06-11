# CLAUDE.md — SIARSIP Manajemen FEB UNM

> Letakkan file ini di root repository. Claude Code akan membacanya otomatis di setiap sesi.

## Tentang Proyek

Sistem Informasi Arsip Digital Program Studi Manajemen FEB Universitas Negeri Makassar. Repositori dokumen dengan 3 lapis visibility (public / mahasiswa / internal) + role admin. Frontend publik tanpa login; dokumen terbatas memerlukan login.

## Stack

- Laravel 12, PHP 8.2+ (semula Laravel 11; dinaikkan karena Laravel 11 EOL dengan CVE-2026-48019 yang tidak akan dipatch — disetujui 2026-06-11)
- Filament 3 untuk panel admin (path: `/admin`)
- MySQL 8
- Blade + Tailwind CSS + Alpine.js untuk frontend publik (TANPA React/Vue)
- Laravel Breeze untuk auth
- PDF.js untuk preview dokumen
- Deployment: Hostinger via GitHub auto-deploy

## Aturan Arsitektur (WAJIB)

1. **Visibility per-dokumen, bukan per-kategori.** Kolom `documents.visibility` enum: `public`, `mahasiswa`, `internal`. Hierarki akses: admin/dosen ≥ internal, mahasiswa ≥ mahasiswa, publik hanya public.
2. **File TIDAK PERNAH di public webroot.** Simpan di `storage/app/documents/{kategori}/{tahun}/`. Unduhan dan preview selalu melalui controller `DocumentAccessController` yang memeriksa role via policy/middleware.
3. **Soft delete** untuk model Document. Tidak ada hard delete dari UI.
4. **Setiap unduhan dan upload dicatat** di tabel `activity_logs`.
5. Kategori bersifat dinamis (tabel `categories` dengan `parent_id`), JANGAN hardcode nama kategori di kode.
6. Validasi upload: MIME type server-side (PDF, DOCX, XLSX, PPTX, JPG, PNG), maks 50 MB.
7. Semua label UI dalam **Bahasa Indonesia**.

## Role

| Role | Nilai enum `users.role` | Akses |
|---|---|---|
| Admin | `admin` | Semua + panel Filament |
| Dosen | `dosen` | Dokumen internal, mahasiswa, public |
| Mahasiswa | `mahasiswa` | Dokumen mahasiswa, public |
| Publik | (tanpa login) | Dokumen public saja |

## Skema Database Inti

Lihat `database/migrations/`. Tabel: `users` (+role, identity_number, is_active), `categories` (self-referencing parent_id), `documents` (visibility, academic_year, semester, course_name, lecturer_name, expires_at, status, counters), `activity_logs`. Fase 3: `document_versions`, `accreditation_criteria`, `document_criteria`.

## Konvensi Kode

- Controller tipis, logika di Service class (`app/Services/`)
- Policy untuk otorisasi dokumen: `DocumentPolicy@view`, `@download`
- Route publik di `routes/web.php` grup tanpa middleware; route terproteksi grup `auth` + middleware `role`
- Seeder kategori sesuai struktur 9 kategori prodi (lihat `database/seeders/CategorySeeder.php`)
- Gunakan Bahasa Indonesia untuk: label form, pesan validasi (`lang/id`), nama menu
- Commit message Bahasa Inggris konvensional: `feat:`, `fix:`, `refactor:`

## Perintah Penting

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run dev          # development
npm run build        # sebelum deploy
php artisan test     # jalankan SEBELUM setiap commit besar
```

## Testing

- Feature test wajib untuk: akses dokumen per role (4 role × 3 visibility = 12 kasus), upload, soft delete
- Test otorisasi adalah PRIORITAS — kebocoran dokumen internal tidak boleh terjadi

## Deployment (Hostinger)

- Branch `main` = production, auto-deploy via Git
- Jangan commit: `.env`, `storage/app/documents/`, `node_modules`, `vendor`
- Setelah deploy: `php artisan migrate --force`, `php artisan config:cache`, `npm run build` (atau commit hasil build jika Node tidak tersedia di server)

## Status Fase

- [ ] Fase 1 — MVP (auth, CRUD dokumen, visibility, frontend publik, pencarian)
  - [x] Sesi 1 — Scaffold: Laravel 12 + Breeze (Blade) + Filament 3 di `/admin`, migrasi skema inti (users+role, categories, documents, activity_logs), model + relasi + soft delete, CategorySeeder (9 kategori + 49 sub), AdminUserSeeder, lang/id (2026-06-11)
  - [x] Sesi 2 — Otorisasi: middleware `role`, DocumentPolicy (hierarki visibility + draft hanya admin + user nonaktif = publik), DocumentAccessController (/dokumen/{slug}/unduh & /preview dari disk privat `documents` + activity log + counter), registrasi = mahasiswa nonaktif menunggu approval admin, login menolak akun nonaktif; 62 test pass termasuk matriks 12 role×visibility (2026-06-11)
  - [x] Sesi 3 — Panel admin Filament: DocumentResource (upload ke documents/{kategori}/{tahun}, nama file {slug}-{timestamp}, validasi MIME+50MB, select kategori bertingkat, badge visibility, filter, soft delete tanpa force delete), CategoryResource (cegah hapus jika ada isi), UserResource (toggle aktivasi + badge pendaftar pending + reset password), 4 widget dashboard (statistik, dokumen/kategori, terbaru, MoU <90 hari); log upload/update/delete ke activity_logs; 67 test pass (2026-06-11)
  - [x] Sesi 4 — Frontend publik layout & beranda: layout `layouts/public` (navbar sticky responsive + footer identitas prodi; link menu muncul otomatis via Route::has saat sesi berikut menambah halaman), beranda hero + statistik + grid 9 kategori berikon + dokumen unggulan + dokumen terbaru, tema Tailwind `unm` (#F77F00), komponen x-document-card; hanya dokumen public+published yang tampil; 70 test pass (2026-06-11)
  - [x] Sesi 5 — Arsip & pencarian: /arsip (daftar kategori), /arsip/{kategori} (dokumen kategori+sub, filter tahun akademik, pagination, chip sub-kategori, breadcrumb), /dokumen/{slug} (detail metadata lengkap + preview PDF.js CDN / gambar + dokumen terkait + tombol unduh), /cari?q= (FULLTEXT title+description, fallback LIKE utk kata <3 huruf, hasil sesuai visibility pengunjung); listing pakai scope visibleTo, Category::rootsWithVisibleDocumentCounts dipakai beranda+arsip; SearchTest pakai DatabaseTruncation krn FULLTEXT tak melihat baris belum commit; 86 test pass (2026-06-11)
- [ ] Fase 2 — Bulk upload, import user, statistik
- [ ] Fase 3 — Versioning, notifikasi MoU, mapping akreditasi LAMEMBA

Perbarui checklist ini setiap fitur selesai.
