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

        {{-- Filter tahun akademik --}}
        @if ($years->isNotEmpty())
            <form method="GET" class="mt-6 flex flex-wrap items-center gap-3">
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
        @endif

        {{-- Daftar dokumen --}}
        @if ($documents->isEmpty())
            <div class="mt-8 rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center text-sm text-gray-500">
                Belum ada dokumen pada kategori ini{{ $selectedYear ? " untuk tahun {$selectedYear}" : '' }}.
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
