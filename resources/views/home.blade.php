@extends('layouts.public')

@section('content')

    {{-- Hero --}}
    <section class="bg-gradient-to-br from-unm-500 via-unm-600 to-unm-700 text-white">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
            <div class="max-w-3xl">
                <p class="text-sm font-semibold uppercase tracking-widest text-unm-100">
                    {{ \App\Models\Setting::get('site_owner') }}
                </p>
                <h1 class="mt-3 text-3xl font-bold leading-tight sm:text-5xl">
                    {{ \App\Models\Setting::get('site_name') }} — {{ \App\Models\Setting::get('site_tagline') }}
                </h1>
                <p class="mt-5 max-w-2xl text-base leading-relaxed text-unm-50 sm:text-lg">
                    Akses dokumen akademik, kemahasiswaan, penelitian, dan bukti kinerja
                    akreditasi program studi dalam satu pintu.
                </p>

                @if (Route::has('cari'))
                    <form action="{{ route('cari') }}" method="GET" class="mt-8 flex max-w-xl overflow-hidden rounded-xl bg-white shadow-lg">
                        <input type="search" name="q" placeholder="Cari judul dokumen…"
                               class="w-full border-0 px-5 py-3.5 text-gray-900 placeholder-gray-400 focus:ring-0">
                        <button type="submit" class="bg-unm-800 px-6 text-sm font-semibold text-white transition hover:bg-unm-900">
                            Cari
                        </button>
                    </form>

                    {{-- Pencarian populer: satu klik tanpa mengetik --}}
                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <span class="text-xs font-medium text-unm-100">Populer:</span>
                        @foreach (['Kurikulum', 'RPS', 'Panduan Akademik', 'Akreditasi', 'MBKM'] as $keyword)
                            <a href="{{ route('cari', ['q' => $keyword]) }}"
                               class="rounded-full bg-white/15 px-3 py-1 text-xs font-medium text-white transition hover:bg-white/30">
                                {{ $keyword }}
                            </a>
                        @endforeach
                    </div>
                @endif

                <div class="mt-10 flex flex-wrap gap-8">
                    <div>
                        <div class="text-3xl font-bold">{{ number_format($totalPublicDocuments, 0, ',', '.') }}</div>
                        <div class="mt-1 text-sm text-unm-100">Dokumen publik</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold">{{ $totalCategories }}</div>
                        <div class="mt-1 text-sm text-unm-100">Kategori arsip</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Ajakan masuk: publik tidak tahu ada dokumen di balik login --}}
    @guest
        <section class="border-b border-unm-100 bg-unm-50">
            <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-3.5 sm:px-6 lg:px-8">
                <p class="text-sm text-unm-900">
                    <span class="font-semibold">Mahasiswa atau dosen prodi?</span>
                    Masuk untuk mengakses RPS, modul, dan dokumen internal yang tidak ditampilkan untuk publik.
                </p>
                <a href="{{ route('login') }}"
                   class="shrink-0 rounded-lg bg-unm-500 px-4 py-1.5 text-sm font-semibold text-white transition hover:bg-unm-600">
                    Masuk
                </a>
            </div>
        </section>
    @endguest

    {{-- Dokumen unggulan --}}
    @if ($featuredDocuments->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pt-14 sm:px-6 lg:px-8">
            <h2 class="text-xl font-bold text-gray-900 sm:text-2xl">Dokumen Unggulan</h2>
            <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featuredDocuments as $document)
                    <x-document-card :document="$document" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Grid kategori --}}
    <section class="mx-auto max-w-7xl px-4 pt-14 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900 sm:text-2xl">Jelajahi Kategori Arsip</h2>
                <p class="mt-1 text-sm text-gray-600">Dokumen tersusun dalam {{ $totalCategories }} kategori utama program studi.</p>
            </div>
        </div>

        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($categories as $category)
                @php
                    try {
                        $iconHtml = svg($category->icon ?: 'heroicon-o-folder', 'h-7 w-7')->toHtml();
                    } catch (\Throwable) {
                        $iconHtml = svg('heroicon-o-folder', 'h-7 w-7')->toHtml();
                    }
                    $isEmpty = $category->visible_documents_count === 0;
                @endphp
                <div class="flex h-full gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition {{ $isEmpty ? 'opacity-60' : 'hover:border-unm-300 hover:shadow-md' }}">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg {{ $isEmpty ? 'bg-gray-100 text-gray-400' : 'bg-unm-50 text-unm-600' }}">
                        {!! $iconHtml !!}
                    </span>
                    <div>
                        <h3 class="font-semibold {{ $isEmpty ? 'text-gray-600' : 'text-gray-900' }}">
                            @if (Route::has('arsip.show'))
                                <a href="{{ route('arsip.show', $category) }}" class="hover:text-unm-600">{{ $category->name }}</a>
                            @else
                                {{ $category->name }}
                            @endif
                        </h3>
                        @if ($category->description)
                            <p class="mt-1 line-clamp-2 text-sm text-gray-600">{{ $category->description }}</p>
                        @endif
                        @if ($isEmpty)
                            <p class="mt-2 text-xs font-medium text-gray-400">Belum ada dokumen publik</p>
                        @else
                            <p class="mt-2 text-xs font-medium text-unm-600">
                                {{ number_format($category->visible_documents_count, 0, ',', '.') }} dokumen publik
                            </p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Paling banyak diunduh --}}
    @if ($popularDocuments->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pt-14 sm:px-6 lg:px-8">
            <h2 class="text-xl font-bold text-gray-900 sm:text-2xl">Paling Banyak Diunduh</h2>
            <p class="mt-1 text-sm text-gray-600">Dokumen yang paling sering dicari pengunjung lain.</p>
            <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($popularDocuments as $document)
                    <x-document-card :document="$document" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Dokumen terbaru --}}
    <section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <h2 class="text-xl font-bold text-gray-900 sm:text-2xl">Dokumen Terbaru</h2>

        @if ($latestDocuments->isEmpty())
            <div class="mt-6 rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center text-sm text-gray-500">
                Belum ada dokumen publik yang diterbitkan.
            </div>
        @else
            <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($latestDocuments as $document)
                    <x-document-card :document="$document" />
                @endforeach
            </div>
        @endif
    </section>

@endsection
