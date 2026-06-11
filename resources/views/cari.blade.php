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

        <form action="{{ route('cari') }}" method="GET"
              class="mt-6 flex max-w-xl overflow-hidden rounded-xl border border-gray-300 bg-white shadow-sm focus-within:border-unm-500">
            <input type="search" name="q" value="{{ $query }}" placeholder="Cari judul atau deskripsi dokumen…"
                   class="w-full border-0 px-5 py-3 text-gray-900 placeholder-gray-400 focus:ring-0" autofocus>
            <button type="submit" class="bg-unm-500 px-6 text-sm font-semibold text-white transition hover:bg-unm-600">
                Cari
            </button>
        </form>

        @if ($query === '')
            <p class="mt-8 text-sm text-gray-500">Masukkan kata kunci untuk mencari dokumen.</p>
        @elseif ($documents->isEmpty())
            <div class="mt-8 rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center text-sm text-gray-500">
                Tidak ditemukan dokumen untuk kata kunci <strong>"{{ $query }}"</strong>.
            </div>
        @else
            <p class="mt-8 text-sm text-gray-600">
                Ditemukan <strong>{{ number_format($documents->total(), 0, ',', '.') }}</strong> dokumen
                untuk kata kunci <strong>"{{ $query }}"</strong>.
            </p>

            <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
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
