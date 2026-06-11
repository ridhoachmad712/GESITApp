# PERENCANAAN SISTEM INFORMASI ARSIP DIGITAL
## Program Studi Manajemen FEB Universitas Negeri Makassar

**Nama sistem (usulan):** SIARSIP Manajemen — Sistem Informasi Arsip Prodi Manajemen
**Versi dokumen:** 1.0 — Juni 2026
**Metode eksekusi:** Pengembangan berbantuan Claude Code, deployment ke Hostinger via GitHub auto-deploy

---

## 1. RINGKASAN EKSEKUTIF

Sistem arsip digital berbasis web untuk mengelola, menyimpan, dan mendistribusikan dokumen Program Studi Manajemen FEB UNM dengan tiga lapis akses (publik, mahasiswa, dosen/internal) ditambah role pengelola (admin). Sistem dirancang untuk:

1. Memudahkan akses dokumen akademik bagi seluruh civitas akademika
2. Menjadi repositori bukti kinerja untuk akreditasi LAMEMBA
3. Menjamin keamanan dokumen internal melalui kontrol akses berbasis dokumen (bukan berbasis kategori)

**Keputusan arsitektur utama:** visibility dikontrol per-dokumen, kategori hanya sebagai struktur navigasi.

---

## 2. STACK TEKNOLOGI

| Komponen | Pilihan | Alasan |
|---|---|---|
| Backend framework | Laravel 12 (semula Laravel 11; dinaikkan karena Laravel 11 EOL + CVE-2026-48019) | Ekosistem PHP dominan di kampus Indonesia, mudah maintenance |
| Admin panel | Filament 3 | CRUD, upload, manajemen user siap pakai — hemat 60-70% waktu |
| Database | MySQL 8 | Standar Hostinger shared/cloud hosting |
| Frontend publik | Blade + Tailwind CSS + Alpine.js | Ringan, SEO-friendly, tanpa build kompleks |
| Preview PDF | PDF.js (embed) | Preview di browser tanpa unduh |
| Storage file | Local storage (`storage/app`) dengan symlink, terstruktur per kategori/tahun | Sederhana untuk Hostinger; migrasi ke S3-compatible bisa belakangan |
| Auth | Laravel Breeze + role middleware | Sederhana, cukup untuk 4 role |
| Pencarian | MySQL FULLTEXT (fase 1) → Laravel Scout + Meilisearch (opsional fase 3) | Bertahap sesuai kebutuhan |
| Deployment | GitHub → Hostinger auto-deploy (webhook/Git deploy) | Workflow yang sudah dikenal |

**Catatan hosting:** pastikan paket Hostinger mendukung PHP 8.2+, Composer, dan storage cukup (estimasi awal 10–20 GB; arsip akreditasi + dokumentasi foto bisa besar). Jika shared hosting terbatas, pertimbangkan Hostinger Cloud/VPS.

---

## 3. ROLE & MATRIKS HAK AKSES

### 3.1 Role

| Role | Cara akses | Deskripsi |
|---|---|---|
| **Publik** | Tanpa login | Pengunjung umum, calon mahasiswa, asesor |
| **Mahasiswa** | Login (NIM + email) | Mahasiswa aktif prodi |
| **Dosen** | Login | Dosen prodi; akses semua dokumen |
| **Admin** | Login | Pengelola prodi: upload, atur visibility, kelola user, lihat log |

Visibility dokumen: `public` < `mahasiswa` < `internal` (dosen+admin). Role lebih tinggi otomatis mewarisi akses di bawahnya.

### 3.2 Matriks Visibility Default per Jenis Dokumen

> Default ini bisa di-override per dokumen oleh admin saat upload.

**Kategori 1 — Profil dan Dokumen Dasar**

| Dokumen | Visibility default |
|---|---|
| Sejarah Program Studi | public |
| Visi, Misi, Tujuan, Strategi | public |
| Struktur Organisasi | public |
| Renstra | mahasiswa (ringkasan public) |
| SOP dan Pedoman Akademik | mahasiswa |
| Buku Evaluasi Penyelesaian Studi | internal |
| Buku Panduan Akademik | public |

**Kategori 2 — Arsip Akademik**

| Dokumen | Visibility default |
|---|---|
| Kurikulum terbaru & peta kurikulum | public |
| CPL | public |
| RPS seluruh mata kuliah | mahasiswa |
| Kontrak kuliah | mahasiswa |
| Modul pembelajaran & bahan ajar | mahasiswa |
| E-book (open access / karya dosen) | mahasiswa |
| Absensi perkuliahan | internal |
| Kisi-kisi UTS/UAS | mahasiswa (terbit terjadwal) |
| Rubrik penilaian & instrumen evaluasi | internal |
| Rekap hasil evaluasi | internal |

**Kategori 3 — Arsip Kemahasiswaan**

| Dokumen | Visibility default |
|---|---|
| Buku Pedoman Kemahasiswaan | mahasiswa |
| Panduan MBKM | public |
| Data prestasi mahasiswa | public |
| Dokumentasi kegiatan | public |
| PKM | mahasiswa |
| Kegiatan ormawa | public |
| Alumni & tracer study | internal (ringkasan public) |

**Kategori 4 — Arsip Dosen**

| Dokumen | Visibility default |
|---|---|
| Profil dosen | public |
| CV dosen | public (versi ringkas) |
| Sertifikat kompetensi | internal |
| BKD & beban mengajar | internal |
| Publikasi ilmiah (daftar + link) | public |
| Buku ajar | public (metadata), file sesuai lisensi |
| HKI | public (sertifikat) |
| Pengabdian masyarakat | public (laporan ringkas) |

**Kategori 5 — Penelitian & Pengabdian**

| Dokumen | Visibility default |
|---|---|
| Roadmap penelitian/pengabdian | public |
| Proposal | internal |
| Laporan | internal (abstrak public) |
| Artikel jurnal & prosiding (daftar/link) | public |
| Dokumentasi kegiatan | public |

**Kategori 6 — Kerja Sama**

| Dokumen | Visibility default |
|---|---|
| Daftar MoU/MoA/IA (judul, mitra, masa berlaku) | public |
| File lengkap MoU/MoA/IA | internal |
| Laporan implementasi & evaluasi | internal |
| Dokumentasi kegiatan | public |

**Kategori 7 — Penjaminan Mutu**

| Dokumen | Visibility default |
|---|---|
| Dokumen SPMI, manual & standar mutu | mahasiswa |
| Formulir mutu | internal |
| AMI & tindak lanjut | internal |
| Survei kepuasan (hasil ringkas) | public; data mentah internal |

**Kategori 8 — Akreditasi**

| Dokumen | Visibility default |
|---|---|
| Sertifikat akreditasi | public |
| LKPS, LED, dokumen pendukung, bukti kinerja | internal |

**Kategori 9 — Dokumentasi** → public (galeri foto/video kegiatan).

---

## 4. DESAIN DATABASE

### 4.1 Skema Tabel Inti

```
users
- id, name, email, password
- role: enum('admin','dosen','mahasiswa')
- identity_number (NIM/NIP/NIDN)
- is_active (boolean)
- timestamps

categories
- id, name, slug, icon, description
- parent_id (nullable, self-reference → sub-kategori)
- sort_order
- timestamps

documents
- id, title, slug, description
- category_id (FK)
- file_path, file_name, file_size, mime_type
- visibility: enum('public','mahasiswa','internal')
- academic_year (varchar, mis. "2025/2026")
- semester: enum('ganjil','genap','-') nullable
- course_name (nullable — untuk RPS, modul, kontrak kuliah)
- lecturer_name (nullable — untuk arsip dosen)
- expires_at (nullable — untuk MoU/MoA)
- uploaded_by (FK users)
- download_count, view_count
- is_featured (boolean)
- status: enum('published','draft','archived')
- timestamps, soft_deletes

document_versions  (fase 3)
- id, document_id, file_path, version_number, notes, uploaded_by, created_at

activity_logs
- id, user_id (nullable untuk publik), document_id
- action: enum('view','download','upload','update','delete')
- ip_address, user_agent, created_at

accreditation_criteria  (fase 3)
- id, code (mis. "K6"), name, instrument ("LAMEMBA")

document_criteria  (pivot, fase 3)
- document_id, criteria_id
```

### 4.2 Aturan Bisnis Penting

1. File disimpan di luar public root; unduhan lewat controller yang memeriksa role → mencegah akses langsung via URL tebakan.
2. Soft delete untuk dokumen — arsip tidak boleh hilang permanen dari panel admin.
3. Format file diizinkan: PDF, DOCX, XLSX, PPTX, JPG, PNG, MP4 (dokumentasi). Maks 50 MB per file (video via link YouTube/Drive).
4. Penamaan file otomatis: `{kategori}/{tahun}/{slug}-{timestamp}.{ext}`.
5. MoU dengan `expires_at` < 90 hari → muncul peringatan di dashboard admin.

---

## 5. SPESIFIKASI FITUR PER FASE

### FASE 1 — MVP (target: 4–6 minggu kerja efektif)

**Publik (frontend):**
- F1.1 Beranda: hero profil prodi, statistik (jumlah dokumen, kategori), dokumen terbaru/featured
- F1.2 Halaman kategori: daftar dokumen public, filter tahun akademik
- F1.3 Halaman detail dokumen: metadata, preview PDF (PDF.js), tombol unduh
- F1.4 Pencarian: judul + deskripsi (MySQL FULLTEXT)
- F1.5 Halaman statis: Sejarah, VMTS, Struktur Organisasi
- F1.6 Halaman daftar kerja sama (tabel mitra, jenis, masa berlaku — tanpa file)
- F1.7 Halaman profil dosen (grid kartu dosen)

**Terproteksi (backend login):**
- F1.8 Auth: login, lupa password; registrasi mahasiswa via approval admin ATAU import massal CSV
- F1.9 Dashboard sesuai role: daftar dokumen yang bisa diakses + pencarian
- F1.10 Panel admin (Filament): CRUD dokumen + upload, CRUD kategori, CRUD user, set visibility
- F1.11 Middleware visibility pada route unduhan & preview
- F1.12 Activity log dasar (upload, download)

### FASE 2 — Pengisian Konten & Penyempurnaan (paralel/setelah MVP)

- F2.1 Bulk upload (zip atau multi-file) dengan metadata massal
- F2.2 Import user mahasiswa & dosen dari Excel
- F2.3 Statistik dashboard admin: dokumen per kategori, unduhan terbanyak, dokumen kosong per kategori (gap analysis)
- F2.4 SOP pengisian: penanggung jawab per kategori (lihat §7)

### FASE 3 — Fitur Lanjutan

- F3.1 Versioning dokumen (riwayat revisi RPS, kurikulum)
- F3.2 Notifikasi MoU/MoA kedaluwarsa (email + dashboard)
- F3.3 Tagging dokumen ke kriteria akreditasi LAMEMBA → halaman "Bundel Akreditasi" yang memfilter bukti per kriteria
- F3.4 Pencarian full-text isi PDF (Laravel Scout + Meilisearch / ekstraksi teks)
- F3.5 Galeri dokumentasi (kategori 9) dengan album per kegiatan
- F3.6 Laporan PDF otomatis: rekap arsip per semester untuk rapat prodi

---

## 6. STRUKTUR HALAMAN (SITEMAP)

```
PUBLIK
├── / (Beranda)
├── /profil
│   ├── /sejarah  /visi-misi  /struktur-organisasi  /dosen
├── /arsip
│   ├── /arsip/{kategori-slug}
│   └── /dokumen/{slug}  (detail + preview)
├── /kerjasama  (daftar MoU/MoA)
├── /dokumentasi  (galeri)
├── /cari?q=
└── /login

TERPROTEKSI
├── /dashboard  (sesuai role)
├── /dokumen-internal  (browse + filter)
└── /admin  (Filament: dokumen, kategori, user, log, statistik)
```

---

## 7. TATA KELOLA & PENGISIAN KONTEN

Titik gagal terbesar sistem arsip kampus adalah konten kosong. Mitigasi:

| Kategori | Penanggung jawab pengisian | Frekuensi |
|---|---|---|
| 1. Profil & Dokumen Dasar | Admin prodi | Sekali + update tahunan |
| 2. Arsip Akademik (RPS, modul) | Koordinator MK / GPM | Tiap awal semester |
| 3. Kemahasiswaan | Pembina kemahasiswaan + ormawa | Per kegiatan |
| 4. Arsip Dosen | Masing-masing dosen (atau admin) | Update tahunan |
| 5. Penelitian & Pengabdian | Koordinator penelitian | Per hibah/kegiatan |
| 6. Kerja Sama | Admin prodi | Per MoU baru |
| 7. Penjaminan Mutu | UPM/GPM prodi | Per siklus AMI |
| 8. Akreditasi | Tim akreditasi | Per siklus |

**Langkah administratif sebelum coding:** (a) persetujuan/SK Kaprodi tentang sistem & penanggung jawab, (b) inventarisasi dokumen eksisting dalam spreadsheet (judul, kategori, tahun, visibility), (c) verifikasi hak cipta e-book — hanya open access dan karya dosen sendiri yang diunggah.

---

## 8. KEAMANAN & BACKUP

1. HTTPS wajib (Hostinger menyediakan SSL gratis)
2. Rate limiting pada login & endpoint unduhan
3. Validasi MIME type server-side, bukan hanya ekstensi
4. File internal di luar webroot, diakses via signed/authorized route
5. Backup: `spatie/laravel-backup` → backup DB harian + file mingguan ke Google Drive/storage eksternal. **Arsip akreditasi hilang = bencana.**
6. Password policy minimal 8 karakter; admin wajib ganti password default

---

## 9. TIMELINE EKSEKUSI (dengan Claude Code)

| Minggu | Aktivitas |
|---|---|
| 0 | Persiapan: SK kaprodi, inventarisasi dokumen, finalisasi matriks visibility, setup repo GitHub + Hostinger |
| 1 | Sesi Claude Code 1–3: scaffold Laravel + Filament, migrasi DB, seeder kategori, auth + role |
| 2 | Sesi 4–6: panel admin (CRUD dokumen, upload, visibility), middleware akses |
| 3 | Sesi 7–9: frontend publik (beranda, kategori, detail, preview PDF, pencarian) |
| 4 | Sesi 10–11: halaman profil, kerja sama, dashboard mahasiswa/dosen, activity log |
| 5 | Testing internal, perbaikan, import user, deploy production |
| 6 | Pelatihan admin & dosen, mulai pengisian konten (Fase 2 berjalan paralel) |

Estimasi realistis dengan Claude Code: **5–6 minggu** sampai MVP live, dengan asumsi 2–3 sesi pengembangan per minggu.

---

## 10. INDIKATOR KEBERHASILAN

1. MVP live di domain resmi dalam ≤ 8 minggu
2. ≥ 80% jenis dokumen kategori 1–3 terisi dalam 3 bulan pertama
3. Seluruh RPS semester berjalan terunggah sebelum minggu ke-2 perkuliahan
4. Zero insiden kebocoran dokumen internal
5. Saat simulasi akreditasi: bukti per kriteria dapat ditemukan < 2 menit
