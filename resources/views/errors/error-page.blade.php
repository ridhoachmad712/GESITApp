@extends('layouts.public')

@section('title', $kode.' — '.$judul)

@section('content')
    <section class="mx-auto flex max-w-3xl flex-col items-center px-4 py-24 text-center sm:px-6">
        <p class="text-7xl font-bold text-unm-200 sm:text-8xl">{{ $kode }}</p>
        <h1 class="mt-4 text-2xl font-bold text-gray-900 sm:text-3xl">{{ $judul }}</h1>
        <p class="mt-3 max-w-md text-sm leading-relaxed text-gray-600">{{ $pesan }}</p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('home') }}"
               class="rounded-lg bg-unm-500 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-unm-600">
                Kembali ke Beranda
            </a>
            <a href="{{ route('arsip.index') }}"
               class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 transition hover:border-unm-400 hover:text-unm-700">
                Jelajahi Arsip
            </a>
        </div>
    </section>
@endsection
