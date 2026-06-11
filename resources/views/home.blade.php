@extends('layouts.public')

@section('content')

    {{-- Hero --}}
    <section class="bg-gradient-to-br from-unm-500 via-unm-600 to-unm-700 text-white">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
            <div class="max-w-3xl">
                <p class="text-sm font-semibold uppercase tracking-widest text-unm-100">
                    Program Studi Manajemen FEB UNM
                </p>
                <h1 class="mt-3 text-3xl font-bold leading-tight sm:text-5xl">
                    Arsip Digital Program Studi Manajemen
                </h1>
                <p class="mt-5 max-w-2xl text-base leading-relaxed text-unm-50 sm:text-lg">
                    Akses dokumen akademik, kemahasiswaan, penelitian, dan bukti kinerja akreditasi
                    Program Studi Manajemen Fakultas Ekonomi dan Bisnis Universitas Negeri Makassar
                    dalam satu pintu.
                </p>

                @if (Route::has('cari'))
                    <form action="{{ route('cari') }}" method="GET" class="mt-8 flex max-w-xl overflow-hidden rounded-xl bg-white shadow-lg">
                        <input type="search" name="q" placeholder="Cari judul dokumen…"
                               class="w-full border-0 px-5 py-3.5 text-gray-900 placeholder-gray-400 focus:ring-0">
                        <button type="submit" class="bg-unm-800 px-6 text-sm font-semibold text-white transition hover:bg-unm-900">
                            Cari
                        </button>
                    </form>
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
                @endphp
                <div class="flex h-full gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-unm-300 hover:shadow-md">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-unm-50 text-unm-600">
                        {!! $iconHtml !!}
                    </span>
                    <div>
                        <h3 class="font-semibold text-gray-900">
                            @if (Route::has('arsip.show'))
                                <a href="{{ route('arsip.show', $category) }}" class="hover:text-unm-600">{{ $category->name }}</a>
                            @else
                                {{ $category->name }}
                            @endif
                        </h3>
                        @if ($category->description)
                            <p class="mt-1 line-clamp-2 text-sm text-gray-600">{{ $category->description }}</p>
                        @endif
                        <p class="mt-2 text-xs font-medium text-unm-600">
                            {{ number_format($category->public_documents_count, 0, ',', '.') }} dokumen publik
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

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
