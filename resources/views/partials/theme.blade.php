{{-- Variabel warna tema dari Pengaturan Tampilan (admin) — tanpa build ulang --}}
@php
    $themeShades = \App\Support\ColorPalette::shades(
        \App\Models\Setting::get('primary_color') ?? '#1E3A8A',
    );
@endphp
<style>
    :root {
        @foreach ($themeShades as $shade => $rgb)
            --unm-{{ $shade }}: {{ $rgb }};
        @endforeach
    }
</style>
