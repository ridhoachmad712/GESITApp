<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Backup (spatie/laravel-backup) — arsip akreditasi hilang = bencana.
// DB setiap hari; file dokumen seminggu sekali; bersihkan backup lama dulu.
Schedule::command('backup:clean')->dailyAt('01:00');
Schedule::command('backup:run --only-db')->dailyAt('01:30');
Schedule::command('backup:run')->weeklyOn(7, '02:00'); // Minggu 02:00 — DB + file dokumen
