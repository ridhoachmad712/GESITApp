<?php

use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\BundelAkreditasiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentAccessController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DokumentasiController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Arsip publik & pencarian — otorisasi per dokumen ditangani DocumentPolicy,
// sehingga route ini juga melayani pengunjung tanpa login.
Route::get('/arsip', [ArchiveController::class, 'index'])->name('arsip.index');
Route::get('/arsip/{category:slug}', [ArchiveController::class, 'show'])->name('arsip.show');
Route::get('/dokumentasi', [DokumentasiController::class, 'index'])->name('dokumentasi.index');
Route::get('/cari', [SearchController::class, 'index'])->name('cari');
Route::get('/cari/saran', [SearchController::class, 'suggest'])
    ->middleware('throttle:60,1')
    ->name('cari.saran');

// Akses dokumen — detail, unduh, dan preview file dari storage privat.
Route::get('/dokumen/{document:slug}', [DocumentController::class, 'show'])
    ->name('documents.show');
Route::get('/dokumen/{document:slug}/unduh', [DocumentAccessController::class, 'download'])
    ->middleware('throttle:downloads')
    ->name('documents.download');
Route::get('/dokumen/{document:slug}/preview', [DocumentAccessController::class, 'preview'])
    ->middleware('throttle:downloads')
    ->name('documents.preview');
// Gambar galeri (public+published saja, tanpa log) — tanpa throttle unduhan
// karena satu halaman galeri memuat banyak gambar sekaligus
Route::get('/dokumen/{document:slug}/gambar', [DocumentAccessController::class, 'image'])
    ->name('documents.image');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

// Bundel Akreditasi — bukti per kriteria LAMEMBA, khusus dosen & admin
Route::get('/bundel-akreditasi', [BundelAkreditasiController::class, 'index'])
    ->middleware(['auth', 'role:admin,dosen'])
    ->name('bundel.index');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

require __DIR__.'/auth.php';
