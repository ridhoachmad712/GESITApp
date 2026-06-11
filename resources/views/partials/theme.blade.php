{{-- Variabel warna tema dari Pengaturan Tampilan (admin) — tanpa build ulang --}}
@php
    $themeShades = \App\Support\ColorPalette::shades(
        \App\Models\Setting::get('primary_color') ?? '#1E3A8A',
    );

    [$tr, $tg, $tb] = \App\Support\ColorPalette::hexToRgb(\App\Models\Setting::get('primary_color') ?? '#1E3A8A');
    $faviconSvg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">'
        .sprintf('<rect width="64" height="64" rx="14" fill="#%02X%02X%02X"/>', $tr, $tg, $tb)
        .'<text x="32" y="45" font-family="Arial, sans-serif" font-size="38" font-weight="bold" fill="#ffffff" text-anchor="middle">'
        .e(\Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(\App\Models\Setting::get('site_name') ?? 'G', 0, 1)))
        .'</text></svg>';
@endphp
<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,{{ rawurlencode($faviconSvg) }}">
<style>
    :root {
        @foreach ($themeShades as $shade => $rgb)
            --unm-{{ $shade }}: {{ $rgb }};
        @endforeach
    }
</style>
