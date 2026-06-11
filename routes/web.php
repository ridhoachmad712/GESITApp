<?php

use App\Http\Controllers\DocumentAccessController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Akses file dokumen — tanpa middleware auth karena dokumen public boleh
// diunduh pengunjung; otorisasi per dokumen ditangani DocumentPolicy.
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
