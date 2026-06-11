<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        @php
            $siteName = \App\Models\Setting::get('site_name');
            $siteOwner = \App\Models\Setting::get('site_owner');
            $siteLogo = \App\Models\Setting::get('logo_path');
        @endphp
        <title>{{ $siteName }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @include('partials.theme')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center bg-gray-50 px-4 py-10">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-3">
                @if ($siteLogo)
                    <img src="{{ Storage::disk('public')->url($siteLogo) }}" alt="Logo {{ $siteName }}" class="h-14 w-14 rounded-xl object-contain">
                @else
                    <span class="flex h-14 w-14 items-center justify-center rounded-xl bg-unm-500 text-xl font-bold text-white">{{ Str::upper(Str::substr($siteName, 0, 2)) }}</span>
                @endif
                <span class="text-center leading-tight">
                    <span class="block text-lg font-bold text-gray-900">{{ $siteName }}</span>
                    <span class="block text-xs text-gray-500">{{ $siteOwner }}</span>
                </span>
            </a>

            <div class="mt-8 w-full bg-white px-6 py-7 shadow-sm ring-1 ring-gray-200 sm:max-w-md sm:rounded-2xl">
                {{ $slot }}
            </div>

            <a href="{{ route('home') }}" class="mt-6 text-sm text-gray-500 transition hover:text-unm-600">
                ← Kembali ke beranda
            </a>
        </div>
    </body>
</html>
