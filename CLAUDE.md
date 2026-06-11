# CLAUDE.md — GESIT (Gerakan Sistem Informasi Terpadu) — Prodi Manajemen FEB UNM

> Letakkan file ini di root repository. Claude Code akan membacanya otomatis di setiap sesi.

## Tentang Proyek

GESIT (Gerakan Sistem Informasi Terpadu) — sebelumnya bernama SIARSIP — sistem arsip digital Program Studi Manajemen FEB Universitas Negeri Makassar. Repositori dokumen dengan 3 lapis visibility (public / mahasiswa / internal) + role admin. Frontend publik tanpa login; dokumen terbatas memerlukan login. **Aplikasi murni arsip** (keputusan user 2026-06-11): TANPA halaman profil prodi, dosen, maupun kerja sama. Identitas situs (nama/tagline/pemilik) dan warna dasar diatur dari tabel `settings` via panel admin → Pengaturan Tampilan (warna pakai CSS variables, tanpa build ulang).

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

Lihat `database/migrations/`. Tabel: `users` (+role, identity_number, is_active), `categories` (self-referencing parent_id), `documents` (visibility, academic_year, semester, course_name, lecturer_name, expires_at, status, counters), `activity_logs`, `settings` (key-value: site_name, site_tagline, site_owner, primary_color). Fase 3: `document_versions`, `accreditation_criteria`, `document_criteria`.

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

- [x] **LIVE di https://gesit.manajemenunm.com (2026-06-11)** — Hostinger, app di `~/gesit-app`, webroot symlink `~/domains/manajemenunm.com/public_html/gesit` → `~/gesit-app/public`. Catatan server: PHP `symlink()`+`exec()` DINONAKTIFKAN — `storage:link` tidak bisa, symlink dibuat manual via shell `ln -s`; backup mysqldump perlu diuji (proc_open mungkin diblokir juga). Belum terpasang: cron scheduler, ganti password admin, cek upload limit PHP
- [ ] Fase 1 — MVP (auth, CRUD dokumen, visibility, frontend publik, pencarian)
  - [x] Sesi 1 — Scaffold: Laravel 12 + Breeze (Blade) + Filament 3 di `/admin`, migrasi skema inti (users+role, categories, documents, activity_logs), model + relasi + soft delete, CategorySeeder (9 kategori + 49 sub), AdminUserSeeder, lang/id (2026-06-11)
  - [x] Sesi 2 — Otorisasi: middleware `role`, DocumentPolicy (hierarki visibility + draft hanya admin + user nonaktif = publik), DocumentAccessController (/dokumen/{slug}/unduh & /preview dari disk privat `documents` + activity log + counter), registrasi = mahasiswa nonaktif menunggu approval admin, login menolak akun nonaktif; 62 test pass termasuk matriks 12 role×visibility (2026-06-11)
  - [x] Sesi 3 — Panel admin Filament: DocumentResource (upload ke documents/{kategori}/{tahun}, nama file {slug}-{timestamp}, validasi MIME+50MB, select kategori bertingkat, badge visibility, filter, soft delete tanpa force delete), CategoryResource (cegah hapus jika ada isi), UserResource (toggle aktivasi + badge pendaftar pending + reset password), 4 widget dashboard (statistik, dokumen/kategori, terbaru, MoU <90 hari); log upload/update/delete ke activity_logs; 67 test pass (2026-06-11)
  - [x] Sesi 4 — Frontend publik layout & beranda: layout `layouts/public` (navbar sticky responsive + footer identitas prodi; link menu muncul otomatis via Route::has saat sesi berikut menambah halaman), beranda hero + statistik + grid 9 kategori berikon + dokumen unggulan + dokumen terbaru, tema Tailwind `unm`, komponen x-document-card; hanya dokumen public+published yang tampil; 70 test pass (2026-06-11). Catatan: warna dasar awalnya oranye #F77F00, diganti **biru navy #1E3A8A** atas permintaan user (2026-06-11) — palet di tailwind.config.js (nama kelas tetap `unm`), Filament panel, chart widget, dan avatar dosen
  - [x] Sesi 5 — Arsip & pencarian: /arsip (daftar kategori), /arsip/{kategori} (dokumen kategori+sub, filter tahun akademik, pagination, chip sub-kategori, breadcrumb), /dokumen/{slug} (detail metadata lengkap + preview PDF.js CDN / gambar + dokumen terkait + tombol unduh), /cari?q= (FULLTEXT title+description, fallback LIKE utk kata <3 huruf, hasil sesuai visibility pengunjung); listing pakai scope visibleTo, Category::rootsWithVisibleDocumentCounts dipakai beranda+arsip; SearchTest pakai DatabaseTruncation krn FULLTEXT tak melihat baris belum commit; 86 test pass (2026-06-11)
  - [x] Sesi 6 — Profil & kerja sama: tabel pages/lecturers/agreements + model + factory; /profil (ikhtisar), /profil/{slug} halaman statis editable via Filament RichEditor (PageSeeder: sejarah, visi-misi, struktur-organisasi), /profil/dosen grid kartu dosen (foto di disk public, NIDN, bidang, link SINTA; hanya is_active), /kerjasama tabel MoU/MoA/IA publik (mitra, jenis, masa berlaku, status Aktif/Kedaluwarsa, TANPA tautan file — dites eksplisit); Filament: PageResource, LecturerResource, AgreementResource (grup "Konten Situs", agreements bisa ditautkan ke dokumen internal); plugin tailwindcss/typography; 95 test pass (2026-06-11)
  - [x] Sesi 7 — Dashboard user login: /dashboard (DashboardController) dokumen published sesuai role via visibleTo + filter kategori (select bertingkat dari Category::groupedSelectOptions, juga dipakai DocumentResource), tahun, semester, pencarian LIKE (judul/deskripsi/MK/dosen); redirect pasca-login per role (admin → /admin, lainnya → /dashboard) di AuthenticatedSessionController; link Beranda di nav Breeze; halaman akun /profile (ganti password) bawaan Breeze; 104 test pass (2026-06-11)
  - [x] Sesi 8 — Pengamanan & persiapan deploy: throttle `downloads` 30/menit per user/IP di route unduh+preview (login sudah 5x/menit dari Breeze), spatie/laravel-backup 10 (jadwal di routes/console.php: clean 01.00, DB harian 01.30, full+file dokumen Minggu 02.00; kompresi gzip via BACKUP_COMPRESS_DB; tujuan via BACKUP_DISK — default local, production wajib eksternal; mysqldump path via DB_DUMP_BINARY_PATH utk XAMPP; backup DB+full teruji lokal), SecurityHardeningTest (throttle, login throttle, audit URL /storage tidak membocorkan file privat), .env.example produksi lengkap, README instruksi deploy Hostinger, public/build di-commit (server tanpa Node); 108 test pass (2026-06-11). Kode sudah di-push ke GitHub https://github.com/ridhoachmad712/GESITApp (branch main); deploy Hostinger MENUNGGU: Git deploy hPanel + kredensial DB/domain dari user. PERINGATAN: repo masih PUBLIK — sarankan private + ganti password admin seeder
  - [x] Sesi 9 — Perombakan GESIT murni arsip (permintaan user, dikonfirmasi 2026-06-11): (1) HAPUS TOTAL fitur profil/dosen/kerja sama — model Page/Lecturer/Agreement, controller, view, resource Filament, migrasi, seeder, factory, test (fitur Sesi 6 dianulir); (2) rebranding SIARSIP → GESIT, identitas prodi tetap tampil; (3) tabel `settings` (key-value, cache) + halaman Filament "Pengaturan Tampilan" (nama/tagline/pemilik situs + ColorPicker warna dasar): palet unm di tailwind.config.js jadi CSS variables (rgb var --unm-50..900), di-inject partials/theme.blade.php ke layout public+app+guest dari ColorPalette::shades() (mix putih/hitam dari 1 hex), Filament colors+brandName+chart ikut setting — ganti warna TANPA build ulang; (4) sidebar admin grup "Kategori Arsip": 9 kategori utama dinamis via bootUsing (lazy) → DocumentResource terfilter kategori_utama (termasuk sub); 105 test pass (2026-06-11)
- [ ] Fase 2 — Bulk upload, import user, statistik
- [ ] Fase 3 — Versioning, notifikasi MoU, mapping akreditasi LAMEMBA

Perbarui checklist ini setiap fitur selesai.
