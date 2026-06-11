<?php

use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\DocumentAccessController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Arsip publik & pencarian — otorisasi per dokumen ditangani DocumentPolicy,
// sehingga route ini juga melayani pengunjung tanpa login.
Route::get('/arsip', [ArchiveController::class, 'index'])->name('arsip.index');
Route::get('/arsip/{category:slug}', [ArchiveController::class, 'show'])->name('arsip.show');
Route::get('/cari', [SearchController::class, 'index'])->name('cari');

// Akses dokumen — detail, unduh, dan preview file dari storage privat.
Route::get('/dokumen/{document:slug}', [DocumentController::class, 'show'])
    ->name('documents.show');
Route::get('/dokumen/{document:slug}/unduh', [DocumentAccessController::class, 'download'])
    ->name('documents.download');
Route::get('/dokumen/{document:slug}/preview', [DocumentAccessController::class, 'preview'])
    ->name('documents.preview');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
