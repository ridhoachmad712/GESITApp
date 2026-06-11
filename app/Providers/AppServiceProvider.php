<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Batas unduhan/preview file: 30 permintaan per menit per user/IP.
        // (Login sudah dibatasi 5x/menit oleh LoginRequest bawaan Breeze.)
        RateLimiter::for('downloads', function (Request $request): Limit {
            return Limit::perMinute(30)->by(
                $request->user()?->id !== null ? 'user:'.$request->user()->id : 'ip:'.$request->ip(),
            )->response(function () {
                return response('Terlalu banyak permintaan unduhan. Coba lagi dalam satu menit.', 429);
            });
        });
    }
}
