@extends('layouts.public')

@section('title', 'Dosen')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <nav class="text-sm text-gray-500">
            <a href="{{ route('home') }}" class="hover:text-unm-600">Beranda</a>
            <span class="mx-2">/</span>
            <a href="{{ route('profil.index') }}" class="hover:text-unm-600">Profil</a>
            <span class="mx-2">/</span>
            <span class="font-medium text-gray-900">Dosen</span>
        </nav>

        <h1 class="mt-4 text-2xl font-bold text-gray-900 sm:text-3xl">Dosen Program Studi Manajemen</h1>

        @if ($lecturers->isEmpty())
            <div class="mt-8 rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center text-sm text-gray-500">
                Data dosen belum tersedia.
            </div>
        @else
            <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($lecturers as $lecturer)
                    <div class="flex h-full flex-col items-center rounded-xl border border-gray-200 bg-white p-6 text-center shadow-sm transition hover:border-unm-300 hover:shadow-md">
                        @if ($lecturer->photo_path)
                            <img src="{{ Storage::disk('public')->url($lecturer->photo_path) }}"
                                 alt="{{ $lecturer->name }}"
                                 class="h-24 w-24 rounded-full object-cover ring-2 ring-unm-100">
                        @else
                            <span class="flex h-24 w-24 items-center justify-center rounded-full bg-unm-50 text-2xl font-bold text-unm-600 ring-2 ring-unm-100">
                                {{ Str::of($lecturer->name)->replaceMatches('/[^A-Za-z ]/', '')->trim()->substr(0, 1) }}
                            </span>
                        @endif

                        <h2 class="mt-4 font-semibold leading-snug text-gray-900">{{ $lecturer->name }}</h2>

                        @if ($lecturer->nidn)
                            <p class="mt-1 text-xs text-gray-500">NIDN {{ $lecturer->nidn }}</p>
                        @endif
                        @if ($lecturer->position)
                            <p class="mt-1 text-xs text-gray-500">{{ $lecturer->position }}</p>
                        @endif
                        @if ($lecturer->expertise)
                            <p class="mt-2 rounded-full bg-unm-50 px-3 py-1 text-xs font-medium text-unm-700">
                                {{ $lecturer->expertise }}
                            </p>
                        @endif

                        @if ($lecturer->publication_url)
                            <a href="{{ $lecturer->publication_url }}" target="_blank" rel="noopener"
                               class="mt-auto pt-4 text-xs font-semibold text-unm-600 hover:text-unm-700">
                                Lihat Publikasi ↗
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </section>
@endsection
