<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class SettingSeeder extends Seeder
{
    /**
     * Nilai awal pengaturan situs — selanjutnya diubah admin
     * lewat menu Pengaturan Tampilan.
     */
    public function run(): void
    {
        foreach (Setting::DEFAULTS as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget(Setting::CACHE_KEY);
    }
}
