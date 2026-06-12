@extends('layouts.public')

@section('title', $category->name)

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <nav class="text-sm text-gray-500">
            <a href="{{ route('home') }}" class="hover:text-unm-600">Beranda</a>
            <span class="mx-2">/</span>
            <a href="{{ route('arsip.index') }}" class="hover:text-unm-600">Arsip</a>
            <span class="mx-2">/</span>
            <span class="font-medium text-gray-900">{{ $category->name }}</span>
        </nav>

        <h1 class="mt-4 text-2xl font-bold text-gray-900 sm:text-3xl">{{ $category->name }}</h1>
        @if ($category->description)
            <p class="mt-2 max-w-2xl text-sm text-gray-600">{{ $category->description }}</p>
        @endif

        {{-- Pencarian terbatas kategori ini --}}
        <form action="{{ route('cari') }}" method="GET"
              class="mt-6 flex max-w-xl rounded-xl border border-gray-300 bg-white shadow-sm focus-within:border-unm-500">
            <input type="hidden" name="kategori" value="{{ $category->slug }}">
            <x-search-suggest :kategori="$category->slug" placeholder="Cari di {{ $category->name }}…"
                              input-class="w-full rounded-l-xl border-0 px-5 py-3 text-gray-900 placeholder-gray-400 focus:ring-0" />
            <button type="submit" class="rounded-r-xl bg-unm-500 px-6 text-sm font-semibold text-white transition hover:bg-unm-600">
                Cari
            </button>
        </form>

        {{-- Kartu sub-kategori --}}
        <h2 class="mt-10 text-sm font-semibold uppercase tracking-wider text-gray-500">Pilih sub-kategori</h2>
        <div class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($category->children as $child)
                @php
                    $stat = $stats->get($child->id);
                    $isEmpty = $stat === null;
                @endphp
                <a href="{{ route('arsip.show', $child) }}"
                   class="flex h-full flex-col rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition {{ $isEmpty ? 'opacity-60' : 'hover:border-unm-300 hover:shadow-md' }}">
                    <h3 class="font-semibold {{ $isEmpty ? 'text-gray-600' : 'text-gray-900' }}">{{ $child->name }}</h3>
                    @if ($isEmpty)
                        <p class="mt-2 text-xs font-medium text-gray-400">Belum ada dokumen</p>
                    @else
                        <p class="mt-2 text-xs font-medium text-unm-600">
                            {{ number_format($stat->aggregate, 0, ',', '.') }} dokumen
                        </p>
                        <p class="mt-1 text-xs text-gray-500">
                            Diperbarui {{ \Illuminate\Support\Carbon::parse($stat->latest)->translatedFormat('d M Y') }}
                        </p>
                    @endif
                </a>
            @endforeach
        </div>

        {{-- Jalan pintas: semua dokumen kategori ini --}}
        <div class="mt-8">
            <a href="{{ route('arsip.show', ['category' => $category, 'semua' => 1]) }}"
               class="inline-flex items-center gap-2 text-sm font-semibold text-unm-600 hover:text-unm-700">
                Lihat semua {{ number_format($totalDocuments, 0, ',', '.') }} dokumen di kategori ini
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </a>
        </div>
    </section>
@endsection
