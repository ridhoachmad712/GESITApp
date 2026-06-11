@extends('layouts.public')

@section('title', $page->title)

@section('content')
    <section class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
        <nav class="text-sm text-gray-500">
            <a href="{{ route('home') }}" class="hover:text-unm-600">Beranda</a>
            <span class="mx-2">/</span>
            <a href="{{ route('profil.index') }}" class="hover:text-unm-600">Profil</a>
            <span class="mx-2">/</span>
            <span class="font-medium text-gray-900">{{ $page->title }}</span>
        </nav>

        <h1 class="mt-4 text-2xl font-bold text-gray-900 sm:text-3xl">{{ $page->title }}</h1>

        <article class="prose prose-gray mt-8 max-w-none prose-headings:font-semibold prose-a:text-unm-600">
            {!! $page->content !!}
        </article>

        <p class="mt-10 text-xs text-gray-400">
            Diperbarui {{ $page->updated_at->translatedFormat('d F Y') }}
        </p>
    </section>
@endsection
