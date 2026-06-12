@extends('layouts.public')

@section('title', $query !== '' ? 'Pencarian: '.$query : 'Pencarian')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <nav class="text-sm text-gray-500">
            <a href="{{ route('home') }}" class="hover:text-unm-600">Beranda</a>
            <span class="mx-2">/</span>
            <span class="font-medium text-gray-900">Pencarian</span>
        </nav>

        <h1 class="mt-4 text-2xl font-bold text-gray-900 sm:text-3xl">Pencarian Dokumen</h1>

        <form action="{{ route('cari') }}" method="GET" class="mt-6 max-w-3xl">
            <div class="flex rounded-xl border border-gray-300 bg-white shadow-sm focus-within:border-unm-500">
                <x-search-suggest :kategori="$categoryFilter?->slug" :value="$query"
                                  placeholder="Cari judul atau deskripsi dokumen…"
                                  input-class="w-full rounded-l-xl border-0 px-5 py-3 text-gray-900 placeholder-gray-400 focus:ring-0" />
                <button type="submit" class="rounded-r-xl bg-unm-500 px-6 text-sm font-semibold text-white transition hover:bg-unm-600">
                    Cari
                </button>
            </div>

            {{-- Filter hasil --}}
            <div class="mt-3 flex flex-wrap items-center gap-3">
                <label class="text-sm text-gray-600" for="kategori">Kategori:</label>
                <select id="kategori" name="kategori" onchange="this.form.submit()"
                        class="rounded-lg border-gray-300 text-sm focus:border-unm-500 focus:ring-unm-500">
                    <option value="">Semua kategori</option>
                    @foreach ($categoryOptions as $slug => $name)
                        <option value="{{ $slug }}" @selected($categoryFilter?->slug === $slug)>{{ $name }}</option>
                    @endforeach
                </select>

                @if ($years->isNotEmpty())
                    <label class="text-sm text-gray-600" for="tahun">Tahun:</label>
                    <select id="tahun" name="tahun" onchange="this.form.submit()"
                            class="rounded-lg border-gray-300 text-sm focus:border-unm-500 focus:ring-unm-500">
                        <option value="">Semua tahun</option>
                        @foreach ($years as $year)
                            <option value="{{ $year }}" @selected($selectedYear === $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                @endif

                @if ($categoryFilter || $selectedYear)
                    <a href="{{ route('cari', array_filter(['q' => $query])) }}" class="text-sm text-unm-600 hover:underline">
                        Hapus filter
                    </a>
                @endif
            </div>
        </form>

        @if ($query === '')
            <p class="mt-8 text-sm text-gray-500">Masukkan kata kunci untuk mencari dokumen.</p>
        @elseif ($documents->isEmpty())
            <div class="mt-8 rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center text-sm text-gray-500">
                Tidak ditemukan dokumen untuk kata kunci <strong>"{{ $query }}"</strong>{{ $categoryFilter ? ' di kategori '.$categoryFilter->name : '' }}.
            </div>
        @else
            <p class="mt-8 text-sm text-gray-600">
                Ditemukan <strong>{{ number_format($documents->total(), 0, ',', '.') }}</strong> dokumen
                untuk kata kunci <strong>"{{ $query }}"</strong>{{ $categoryFilter ? ' di kategori '.$categoryFilter->name : '' }}.
            </p>

            {{-- Hasil dengan sorotan kata kunci --}}
            <div class="mt-5 divide-y divide-gray-100 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                @foreach ($documents as $document)
                    <div class="px-5 py-4 transition hover:bg-gray-50">
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            <span class="rounded-full bg-unm-50 px-2.5 py-1 font-medium text-unm-700">{{ $document->category->name }}</span>
                            @if ($document->academic_year)
                                <span class="text-gray-500">{{ $document->academic_year }}</span>
                            @endif
                        </div>
                        <h2 class="mt-2 font-semibold leading-snug">
                            <a href="{{ route('documents.show', $document) }}" class="text-gray-900 hover:text-unm-600">
                                {{ \App\Support\SearchHighlighter::highlight($document->title, $query) }}
                            </a>
                        </h2>
                        @if ($document->description)
                            <p class="mt-1 text-sm text-gray-600">
                                {{ \App\Support\SearchHighlighter::highlight(Str::limit($document->description, 180), $query) }}
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $documents->links() }}
            </div>
        @endif
    </section>
@endsection
