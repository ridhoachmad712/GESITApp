@extends('layouts.public')

@section('title', $category->name)

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <nav class="text-sm text-gray-500">
            <a href="{{ route('home') }}" class="hover:text-unm-600">Beranda</a>
            <span class="mx-2">/</span>
            <a href="{{ route('arsip.index') }}" class="hover:text-unm-600">Arsip</a>
            @if ($category->parent)
                <span class="mx-2">/</span>
                <a href="{{ route('arsip.show', $category->parent) }}" class="hover:text-unm-600">{{ $category->parent->name }}</a>
            @endif
            <span class="mx-2">/</span>
            <span class="font-medium text-gray-900">{{ $category->name }}</span>
        </nav>

        <h1 class="mt-4 text-2xl font-bold text-gray-900 sm:text-3xl">{{ $category->name }}</h1>
        @if ($category->description)
            <p class="mt-2 max-w-2xl text-sm text-gray-600">{{ $category->description }}</p>
        @endif

        {{-- Sub-kategori --}}
        @if ($category->children->isNotEmpty())
            <div class="mt-5 flex flex-wrap gap-2">
                @foreach ($category->children as $child)
                    <a href="{{ route('arsip.show', $child) }}"
                       class="rounded-full border border-gray-200 bg-white px-4 py-1.5 text-sm text-gray-700 transition hover:border-unm-400 hover:text-unm-700">
                        {{ $child->name }}
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Filter tahun akademik + pilihan tampilan --}}
        <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
            @if ($years->isNotEmpty())
                <form method="GET" class="flex flex-wrap items-center gap-3">
                    @if ($viewMode === 'list')
                        <input type="hidden" name="tampilan" value="list">
                    @endif
                    <label for="tahun" class="text-sm font-medium text-gray-700">Tahun akademik:</label>
                    <select id="tahun" name="tahun" onchange="this.form.submit()"
                            class="rounded-lg border-gray-300 text-sm focus:border-unm-500 focus:ring-unm-500">
                        <option value="">Semua tahun</option>
                        @foreach ($years as $year)
                            <option value="{{ $year }}" @selected($selectedYear === $year)>{{ $year }}</option>
                        @endforeach
                    </select>
                    @if ($selectedYear)
                        <a href="{{ route('arsip.show', $category) }}" class="text-sm text-unm-600 hover:underline">Hapus filter</a>
                    @endif
                </form>
            @else
                <span></span>
            @endif

            {{-- Toggle grid / list --}}
            <div class="inline-flex overflow-hidden rounded-lg border border-gray-300 bg-white" role="group" aria-label="Pilihan tampilan">
                <a href="{{ request()->fullUrlWithQuery(['tampilan' => null]) }}"
                   title="Tampilan kartu"
                   class="flex items-center gap-1.5 px-3 py-2 text-sm font-medium transition {{ $viewMode === 'grid' ? 'bg-unm-500 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                    Kartu
                </a>
                <a href="{{ request()->fullUrlWithQuery(['tampilan' => 'list']) }}"
                   title="Tampilan daftar"
                   class="flex items-center gap-1.5 border-l border-gray-300 px-3 py-2 text-sm font-medium transition {{ $viewMode === 'list' ? 'bg-unm-500 text-white' : 'text-gray-600 hover:bg-gray-50' }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                    Daftar
                </a>
            </div>
        </div>

        {{-- Daftar dokumen --}}
        @if ($documents->isEmpty())
            <div class="mt-8 rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center text-sm text-gray-500">
                Belum ada dokumen pada kategori ini{{ $selectedYear ? " untuk tahun {$selectedYear}" : '' }}.
            </div>
        @elseif ($viewMode === 'list')
            <div class="mt-8 divide-y divide-gray-100 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                @foreach ($documents as $document)
                    <x-document-row :document="$document" />
                @endforeach
            </div>

            <div class="mt-8">
                {{ $documents->links() }}
            </div>
        @else
            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($documents as $document)
                    <x-document-card :document="$document" />
                @endforeach
            </div>

            <div class="mt-8">
                {{ $documents->links() }}
            </div>
        @endif
    </section>
@endsection
