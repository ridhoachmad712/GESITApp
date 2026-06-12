@extends('layouts.public')

@section('title', 'Dokumentasi')
@section('meta_description', 'Galeri foto kegiatan Program Studi Manajemen FEB UNM.')

@section('content')
    @php
        $lightboxItems = $photos->map(fn ($photo) => [
            'src' => route('documents.image', $photo),
            'title' => $photo->title,
            'meta' => $photo->category->name.($photo->academic_year ? ' · '.$photo->academic_year : ''),
            'url' => route('documents.show', $photo),
        ])->values();
    @endphp

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8"
             x-data="{ items: {{ \Illuminate\Support\Js::from($lightboxItems) }}, current: null,
                       open(i) { this.current = i }, close() { this.current = null },
                       next() { this.current = (this.current + 1) % this.items.length },
                       prev() { this.current = (this.current - 1 + this.items.length) % this.items.length } }"
             @keydown.escape.window="close()"
             @keydown.arrow-right.window="current !== null && next()"
             @keydown.arrow-left.window="current !== null && prev()">

        <nav class="text-sm text-gray-500">
            <a href="{{ route('home') }}" class="hover:text-unm-600">Beranda</a>
            <span class="mx-2">/</span>
            <span class="font-medium text-gray-900">Dokumentasi</span>
        </nav>

        <div class="mt-4 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Dokumentasi Kegiatan</h1>
                <p class="mt-2 max-w-2xl text-sm text-gray-600">
                    Galeri foto kegiatan program studi. Klik foto untuk memperbesar.
                </p>
            </div>

            @if ($years->isNotEmpty())
                <form method="GET" class="flex items-center gap-2">
                    <label for="tahun" class="text-sm font-medium text-gray-700">Tahun:</label>
                    <select id="tahun" name="tahun" onchange="this.form.submit()"
                            class="rounded-lg border-gray-300 text-sm focus:border-unm-500 focus:ring-unm-500">
                        <option value="">Semua</option>
                        @foreach ($years as $year)
                            <option value="{{ $year }}" @selected($selectedYear === $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>

        @if ($photos->isEmpty())
            <div class="mt-8 rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center text-sm text-gray-500">
                Belum ada foto dokumentasi yang dipublikasikan.
            </div>
        @else
            {{-- Grid foto --}}
            <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-4">
                @foreach ($photos as $index => $photo)
                    <button type="button" @click="open({{ $index }})"
                            class="group relative aspect-square overflow-hidden rounded-xl bg-gray-100 focus:outline-none focus:ring-2 focus:ring-unm-500">
                        <img src="{{ route('documents.image', $photo) }}" alt="{{ $photo->title }}"
                             loading="lazy"
                             class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                        <span class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent px-3 pb-2 pt-8 text-left">
                            <span class="block truncate text-xs font-medium text-white">{{ $photo->title }}</span>
                        </span>
                    </button>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $photos->links() }}
            </div>

            {{-- Lightbox --}}
            <div x-show="current !== null" x-cloak
                 class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-black/90 p-4"
                 @click.self="close()" role="dialog" aria-modal="true">
                <button type="button" @click="close()" aria-label="Tutup"
                        class="absolute right-4 top-4 rounded-full bg-white/10 p-2 text-white hover:bg-white/20">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <button type="button" @click="prev()" aria-label="Sebelumnya"
                        class="absolute left-2 top-1/2 -translate-y-1/2 rounded-full bg-white/10 p-2 text-white hover:bg-white/20 sm:left-6">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                </button>
                <button type="button" @click="next()" aria-label="Berikutnya"
                        class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full bg-white/10 p-2 text-white hover:bg-white/20 sm:right-6">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                </button>

                <template x-if="current !== null">
                    <figure class="flex max-h-full flex-col items-center">
                        <img :src="items[current].src" :alt="items[current].title"
                             class="max-h-[75vh] max-w-full rounded-lg object-contain shadow-2xl">
                        <figcaption class="mt-4 text-center">
                            <span x-text="items[current].title" class="block text-sm font-semibold text-white"></span>
                            <span x-text="items[current].meta" class="mt-0.5 block text-xs text-gray-300"></span>
                            <a :href="items[current].url" class="mt-2 inline-block text-xs font-semibold text-unm-300 hover:text-white">
                                Lihat detail dokumen →
                            </a>
                        </figcaption>
                    </figure>
                </template>
            </div>
        @endif
    </section>
@endsection
