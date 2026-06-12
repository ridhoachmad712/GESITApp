<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $siteName = \App\Models\Setting::get('site_name');
        $siteTagline = \App\Models\Setting::get('site_tagline');
        $siteOwner = \App\Models\Setting::get('site_owner');
        $siteLogo = \App\Models\Setting::get('logo_path');

        // Urutan menu diatur dari admin → Konten Beranda
        $navLinks = collect([
            ['label' => 'Beranda', 'href' => route('home'), 'active' => request()->routeIs('home'), 'order' => (int) \App\Models\Setting::get('nav_beranda_order')],
            ['label' => 'Arsip', 'href' => route('arsip.index'), 'active' => request()->routeIs('arsip.*'), 'order' => (int) \App\Models\Setting::get('nav_arsip_order')],
            ['label' => 'Pencarian', 'href' => route('cari'), 'active' => request()->routeIs('cari'), 'order' => (int) \App\Models\Setting::get('nav_cari_order')],
        ])->sortBy('order')->values();
    @endphp
    <title>@hasSection('title')@yield('title') — {{ $siteName }}@else{{ $siteName }} — {{ $siteTagline }}@endif</title>
    <meta name="description" content="@yield('meta_description', $siteName.' — '.$siteTagline.', arsip digital '.$siteOwner.'.')">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @include('partials.theme')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 font-sans text-gray-900 antialiased">

    {{-- Bar pengumuman (diatur dari admin → Konten Beranda) --}}
    @if (\App\Models\Setting::get('announcement_enabled') === '1' && filled(\App\Models\Setting::get('announcement_text')))
        <div class="bg-unm-900 px-4 py-2 text-center text-sm text-unm-50">
            {{ \App\Models\Setting::get('announcement_text') }}
            @if (filled(\App\Models\Setting::get('announcement_link_url')))
                <a href="{{ \App\Models\Setting::get('announcement_link_url') }}"
                   class="ml-1 font-semibold underline hover:text-white">
                    {{ \App\Models\Setting::get('announcement_link_label') ?: 'Selengkapnya' }}
                </a>
            @endif
        </div>
    @endif

    {{-- Navbar --}}
    <header class="sticky top-0 z-40 border-b border-gray-200 bg-white/95 backdrop-blur" x-data="{ open: false }">
        <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                @if ($siteLogo)
                    <img src="{{ Storage::disk('public')->url($siteLogo) }}" alt="Logo {{ $siteName }}" class="h-10 w-10 rounded-lg object-contain">
                @else
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-unm-500 text-lg font-bold text-white">{{ Str::upper(Str::substr($siteName, 0, 2)) }}</span>
                @endif
                <span class="leading-tight">
                    <span class="block text-sm font-bold sm:text-base">{{ $siteName }}</span>
                    <span class="block text-xs text-gray-500">{{ $siteOwner }}</span>
                </span>
            </a>

            {{-- Menu desktop --}}
            <div class="hidden items-center gap-1 md:flex">
                @foreach ($navLinks as $link)
                    <x-nav-public href="{{ $link['href'] }}" :active="$link['active']">{{ $link['label'] }}</x-nav-public>
                @endforeach

                @auth
                    <a href="{{ auth()->user()->isAdmin() ? '/admin' : route('dashboard') }}"
                       class="ml-3 rounded-lg bg-unm-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-unm-600">
                        Dasbor
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="ml-3 rounded-lg bg-unm-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-unm-600">
                        Masuk
                    </a>
                @endauth
            </div>

            {{-- Tombol menu mobile --}}
            <button type="button" @click="open = !open"
                    class="rounded-md p-2 text-gray-600 hover:bg-gray-100 md:hidden" aria-label="Buka menu">
                <svg x-show="!open" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg x-show="open" x-cloak class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </nav>

        {{-- Menu mobile --}}
        <div x-show="open" x-cloak class="border-t border-gray-100 bg-white px-4 pb-4 pt-2 md:hidden">
            <div class="flex flex-col gap-1">
                @foreach ($navLinks as $link)
                    <x-nav-public href="{{ $link['href'] }}" :active="$link['active']">{{ $link['label'] }}</x-nav-public>
                @endforeach
                @auth
                    <a href="{{ auth()->user()->isAdmin() ? '/admin' : route('dashboard') }}"
                       class="mt-2 rounded-lg bg-unm-500 px-4 py-2 text-center text-sm font-semibold text-white">Dasbor</a>
                @else
                    <a href="{{ route('login') }}"
                       class="mt-2 rounded-lg bg-unm-500 px-4 py-2 text-center text-sm font-semibold text-white">Masuk</a>
                @endauth
            </div>
        </div>
    </header>

    {{-- Konten --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="mt-16 bg-gray-900 text-gray-300">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:grid-cols-2 sm:px-6 lg:grid-cols-3 lg:px-8">
            <div>
                <div class="flex items-center gap-3">
                    @if ($siteLogo)
                        <img src="{{ Storage::disk('public')->url($siteLogo) }}" alt="Logo {{ $siteName }}" class="h-10 w-10 rounded-lg bg-white object-contain p-0.5">
                    @else
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-unm-500 text-lg font-bold text-white">{{ Str::upper(Str::substr($siteName, 0, 2)) }}</span>
                    @endif
                    <span class="text-base font-bold text-white">{{ $siteName }}</span>
                </div>
                <p class="mt-4 text-sm leading-relaxed">
                    {{ $siteTagline }} — arsip digital {{ $siteOwner }}.
                </p>
            </div>
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-white">Tautan</h3>
                <ul class="mt-4 space-y-2 text-sm">
                    @foreach ($navLinks as $link)
                        <li><a href="{{ $link['href'] }}" class="hover:text-unm-300">{{ $link['label'] }}</a></li>
                    @endforeach
                    <li><a href="{{ route('login') }}" class="hover:text-unm-300">Masuk</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-white">Kontak</h3>
                <ul class="mt-4 space-y-2 text-sm leading-relaxed">
                    @if (\App\Models\Setting::get('footer_contact_line1'))
                        <li>{{ \App\Models\Setting::get('footer_contact_line1') }}</li>
                    @endif
                    @if (\App\Models\Setting::get('footer_contact_line2'))
                        <li>{{ \App\Models\Setting::get('footer_contact_line2') }}</li>
                    @endif
                    @if (\App\Models\Setting::get('footer_link_url'))
                        <li>
                            <a href="{{ \App\Models\Setting::get('footer_link_url') }}" target="_blank" rel="noopener" class="hover:text-unm-300">
                                {{ \App\Models\Setting::get('footer_link_label') ?: \App\Models\Setting::get('footer_link_url') }}
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
        <div class="border-t border-gray-800 py-5 text-center text-xs text-gray-500">
            &copy; {{ now()->year }} {{ $siteOwner }}. Hak cipta dilindungi.
        </div>
    </footer>

</body>
</html>
