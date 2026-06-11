@extends('layouts.public')

@section('title', 'Arsip Dokumen')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <nav class="text-sm text-gray-500">
            <a href="{{ route('home') }}" class="hover:text-unm-600">Beranda</a>
            <span class="mx-2">/</span>
            <span class="font-medium text-gray-900">Arsip</span>
        </nav>

        <h1 class="mt-4 text-2xl font-bold text-gray-900 sm:text-3xl">Arsip Dokumen</h1>
        <p class="mt-2 max-w-2xl text-sm text-gray-600">
            Pilih kategori untuk menelusuri dokumen Program Studi Manajemen FEB UNM.
            Dokumen yang tampil mengikuti hak akses Anda.
        </p>

        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($categories as $category)
                @php
                    try {
                        $iconHtml = svg($category->icon ?: 'heroicon-o-folder', 'h-7 w-7')->toHtml();
                    } catch (\Throwable) {
                        $iconHtml = svg('heroicon-o-folder', 'h-7 w-7')->toHtml();
                    }
                @endphp
                <a href="{{ route('arsip.show', $category) }}"
                   class="flex h-full gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-unm-300 hover:shadow-md">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-unm-50 text-unm-600">
                        {!! $iconHtml !!}
                    </span>
                    <div>
                        <h2 class="font-semibold text-gray-900">{{ $category->name }}</h2>
                        @if ($category->description)
                            <p class="mt-1 line-clamp-2 text-sm text-gray-600">{{ $category->description }}</p>
                        @endif
                        <p class="mt-2 text-xs font-medium text-unm-600">
                            {{ number_format($category->visible_documents_count, 0, ',', '.') }} dokumen
                            @if ($category->children->isNotEmpty())
                                · {{ $category->children->count() }} sub-kategori
                            @endif
                        </p>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
@endsection
