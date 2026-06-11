# GESIT — Gerakan Sistem Informasi Terpadu

Sistem arsip digital Program Studi Manajemen, Fakultas Ekonomi dan Bisnis,
Universitas Negeri Makassar. Repositori dokumen dengan tiga lapis akses
(publik / mahasiswa / internal) plus panel admin. Identitas situs (nama,
tagline, pemilik) dan warna dasar dapat diubah dari panel admin
(menu Pengaturan Tampilan) tanpa menyentuh kode.

**Stack:** Laravel 12 · Filament 3 (`/admin`) · MySQL 8 · Blade + Tailwind CSS + Alpine.js · PDF.js

Dokumen spesifikasi lengkap: [CLAUDE.md](CLAUDE.md) dan [PLAN-SIARSIP.md](PLAN-SIARSIP.md).

---

## Menjalankan Secara Lokal

Prasyarat: PHP 8.2+, Composer, MySQL/MariaDB, Node.js 18+.

```bash
composer install && npm install
cp .env.example .env          # lalu isi kredensial DB lokal
php artisan key:generate
php artisan migrate --seed    # 9 kategori arsip, pengaturan situs, akun admin
php artisan storage:link
npm run build                 # atau `npm run dev` saat pengembangan
php artisan serve
```

| Halaman | URL |
|---|---|
| Situs publik | http://127.0.0.1:8000 |
| Login user | http://127.0.0.1:8000/login |
| Panel admin | http://127.0.0.1:8000/admin |

Akun admin hasil seeder: `admin@manajemen-feb.unm.ac.id` — **segera ganti password setelah login pertama**.

Menjalankan test (butuh database `siarsip_testing`, lihat `phpunit.xml`):

```bash
php artisan test
```

---

## Deploy ke Hostinger (Git auto-deploy)

### Sekali saja — persiapan

1. **PHP & ekstensi** — set PHP 8.2+ di hPanel. Pastikan `upload_max_filesize`
   dan `post_max_size` minimal `51M` (hPanel → konfigurasi PHP) agar upload
   dokumen 50 MB berfungsi.
2. **Database** — buat database MySQL + user di hPanel, catat kredensialnya.
3. **Hubungkan repo** — hPanel → Advanced → Git: clone repo ini, branch `main`,
   ke direktori **di luar** `public_html`, misalnya `~/siarsip`.
4. **Arahkan webroot ke `public/`** — file aplikasi tidak boleh berada di
   webroot. Dua pilihan:
   - Ganti `public_html` dengan symlink: `rm -rf ~/public_html && ln -s ~/siarsip/public ~/public_html`
   - Atau (jika symlink tidak diizinkan) deploy ke subdomain yang document
     root-nya bisa diarahkan ke `~/siarsip/public`.
5. **Buat `.env`** di `~/siarsip` dari `.env.example`:
   - `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://domain-anda`
   - Kredensial DB Hostinger; `BACKUP_COMPRESS_DB=true`
   - SMTP Hostinger agar email reset password & notifikasi backup terkirim
6. Jalankan via SSH:

   ```bash
   cd ~/siarsip
   composer install --no-dev --optimize-autoloader
   php artisan key:generate
   php artisan migrate --force --seed
   php artisan storage:link
   php artisan config:cache && php artisan route:cache && php artisan view:cache
   ```

7. **Cron untuk scheduler** (backup harian berjalan dari sini) — hPanel → Cron Jobs:

   ```
   * * * * * cd ~/siarsip && php artisan schedule:run >> /dev/null 2>&1
   ```

### Setiap deploy berikutnya

Push ke branch `main` → Hostinger menarik kode otomatis. Lalu via SSH (atau
jadikan script post-deploy):

```bash
cd ~/siarsip
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

> Asset frontend (`public/build/`) sudah di-commit ke repo karena server
> shared hosting tidak menyediakan Node.js. Setelah mengubah file CSS/JS/Blade,
> jalankan `npm run build` lokal dan commit hasilnya.

---

## Keamanan & Backup

- File dokumen disimpan di `storage/app/documents/` — **di luar webroot**,
  hanya bisa diakses melalui controller yang memeriksa policy per dokumen.
- Login dibatasi 5 percobaan/menit; unduhan & preview 30 permintaan/menit.
- Backup otomatis (spatie/laravel-backup) lewat scheduler:
  - 01.00 — bersihkan backup lama (`backup:clean`)
  - 01.30 — backup database harian (`backup:run --only-db`)
  - Minggu 02.00 — backup penuh DB + file dokumen (`backup:run`)
- **Penting:** default backup tersimpan di disk `local` server yang sama.
  Konfigurasikan tujuan eksternal (S3/FTP/Google Drive) di
  `config/filesystems.php` lalu set `BACKUP_DISK` — arsip akreditasi hilang
  = bencana. Notifikasi kegagalan backup dikirim ke `BACKUP_NOTIFICATION_EMAIL`.
- Cek kesehatan backup: `php artisan backup:list` / `php artisan backup:monitor`.
