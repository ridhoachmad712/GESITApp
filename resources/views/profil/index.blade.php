@extends('layouts.public')

@section('title', 'Profil Program Studi')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <nav class="text-sm text-gray-500">
            <a href="{{ route('home') }}" class="hover:text-unm-600">Beranda</a>
            <span class="mx-2">/</span>
            <span class="font-medium text-gray-900">Profil</span>
        </nav>

        <h1 class="mt-4 text-2xl font-bold text-gray-900 sm:text-3xl">Profil Program Studi Manajemen</h1>
        <p class="mt-2 max-w-2xl text-sm text-gray-600">
            Kenali Program Studi Manajemen Fakultas Ekonomi dan Bisnis Universitas Negeri Makassar.
        </p>

        <div class="mt-8 grid gap-5 sm:grid-cols-2">
            @foreach ($pages as $page)
                <a href="{{ route('profil.show', $page) }}"
                   class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition hover:border-unm-300 hover:shadow-md">
                    <h2 class="font-semibold text-gray-900">{{ $page->title }}</h2>
                    <p class="mt-2 text-sm font-medium text-unm-600">Baca selengkapnya →</p>
                </a>
            @endforeach

            <a href="{{ route('profil.dosen') }}"
               class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition hover:border-unm-300 hover:shadow-md">
                <h2 class="font-semibold text-gray-900">Dosen Program Studi</h2>
                <p class="mt-1 text-sm text-gray-600">{{ $lecturerCount }} dosen aktif</p>
                <p class="mt-2 text-sm font-medium text-unm-600">Lihat profil dosen →</p>
            </a>
        </div>
    </section>
@endsection
