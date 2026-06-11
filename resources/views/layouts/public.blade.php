<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@hasSection('title')@yield('title') — {{ config('app.name') }}@else{{ config('app.name') }} — Arsip Digital Prodi Manajemen FEB UNM @endif</title>
    <meta name="description" content="@yield('meta_description', 'Sistem Informasi Arsip Digital Program Studi Manajemen Fakultas Ekonomi dan Bisnis Universitas Negeri Makassar.')">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 font-sans text-gray-900 antialiased">

    {{-- Navbar --}}
    <header class="sticky top-0 z-40 border-b border-gray-200 bg-white/95 backdrop-blur" x-data="{ open: false }">
        <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-unm-500 text-lg font-bold text-white">SA</span>
                <span class="leading-tight">
                    <span class="block text-sm font-bold sm:text-base">SIARSIP Manajemen</span>
                    <span class="block text-xs text-gray-500">FEB Universitas Negeri Makassar</span>
                </span>
            </a>

            {{-- Menu desktop --}}
            <div class="hidden items-center gap-1 md:flex">
                <x-nav-public href="{{ route('home') }}" :active="request()->routeIs('home')">Beranda</x-nav-public>
                @if (Route::has('profil.index'))
                    <x-nav-public href="{{ route('profil.index') }}" :active="request()->routeIs('profil.*')">Profil</x-nav-public>
                @endif
                @if (Route::has('arsip.index'))
                    <x-nav-public href="{{ route('arsip.index') }}" :active="request()->routeIs('arsip.*')">Arsip</x-nav-public>
                @endif
                @if (Route::has('kerjasama.index'))
                    <x-nav-public href="{{ route('kerjasama.index') }}" :active="request()->routeIs('kerjasama.*')">Kerja Sama</x-nav-public>
                @endif
                @if (Route::has('dokumentasi.index'))
                    <x-nav-public href="{{ route('dokumentasi.index') }}" :active="request()->routeIs('dokumentasi.*')">Dokumentasi</x-nav-public>
                @endif

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
                <x-nav-public href="{{ route('home') }}" :active="request()->routeIs('home')">Beranda</x-nav-public>
                @if (Route::has('profil.index'))
                    <x-nav-public href="{{ route('profil.index') }}" :active="request()->routeIs('profil.*')">Profil</x-nav-public>
                @endif
                @if (Route::has('arsip.index'))
                    <x-nav-public href="{{ route('arsip.index') }}" :active="request()->routeIs('arsip.*')">Arsip</x-nav-public>
                @endif
                @if (Route::has('kerjasama.index'))
                    <x-nav-public href="{{ route('kerjasama.index') }}" :active="request()->routeIs('kerjasama.*')">Kerja Sama</x-nav-public>
                @endif
                @if (Route::has('dokumentasi.index'))
                    <x-nav-public href="{{ route('dokumentasi.index') }}" :active="request()->routeIs('dokumentasi.*')">Dokumentasi</x-nav-public>
                @endif
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
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-unm-500 text-lg font-bold text-white">SA</span>
                    <span class="text-base font-bold text-white">SIARSIP Manajemen</span>
                </div>
                <p class="mt-4 text-sm leading-relaxed">
                    Sistem Informasi Arsip Digital Program Studi Manajemen,
                    Fakultas Ekonomi dan Bisnis, Universitas Negeri Makassar.
                </p>
            </div>
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-white">Tautan</h3>
                <ul class="mt-4 space-y-2 text-sm">
                    <li><a href="{{ route('home') }}" class="hover:text-unm-300">Beranda</a></li>
                    @if (Route::has('arsip.index'))
                        <li><a href="{{ route('arsip.index') }}" class="hover:text-unm-300">Arsip Dokumen</a></li>
                    @endif
                    @if (Route::has('kerjasama.index'))
                        <li><a href="{{ route('kerjasama.index') }}" class="hover:text-unm-300">Kerja Sama</a></li>
                    @endif
                    <li><a href="{{ route('login') }}" class="hover:text-unm-300">Masuk</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-white">Kontak</h3>
                <ul class="mt-4 space-y-2 text-sm leading-relaxed">
                    <li>Kampus UNM Gunung Sari</li>
                    <li>Jl. A. P. Pettarani, Makassar, Sulawesi Selatan</li>
                    <li><a href="https://feb.unm.ac.id" target="_blank" rel="noopener" class="hover:text-unm-300">feb.unm.ac.id</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-gray-800 py-5 text-center text-xs text-gray-500">
            &copy; {{ now()->year }} Program Studi Manajemen FEB UNM. Hak cipta dilindungi.
        </div>
    </footer>

</body>
</html>
