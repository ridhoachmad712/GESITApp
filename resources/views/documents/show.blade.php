@extends('layouts.public')

@section('title', $document->title)
@section('meta_description', Str::limit((string) $document->description, 150))

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <nav class="text-sm text-gray-500">
            <a href="{{ route('home') }}" class="hover:text-unm-600">Beranda</a>
            <span class="mx-2">/</span>
            <a href="{{ route('arsip.index') }}" class="hover:text-unm-600">Arsip</a>
            <span class="mx-2">/</span>
            <a href="{{ route('arsip.show', $document->category) }}" class="hover:text-unm-600">{{ $document->category->name }}</a>
            <span class="mx-2">/</span>
            <span class="font-medium text-gray-900">{{ Str::limit($document->title, 40) }}</span>
        </nav>

        <div class="mt-6 grid gap-8 lg:grid-cols-3">
            {{-- Kolom utama --}}
            <div class="lg:col-span-2">
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="rounded-full bg-unm-50 px-2.5 py-1 font-medium text-unm-700">{{ $document->category->name }}</span>
                    @if ($document->visibility !== \App\Models\Document::VISIBILITY_PUBLIC)
                        <span class="rounded-full bg-amber-100 px-2.5 py-1 font-medium text-amber-800">
                            {{ $document->visibility === \App\Models\Document::VISIBILITY_MAHASISWA ? 'Khusus Mahasiswa' : 'Internal' }}
                        </span>
                    @endif
                </div>

                <h1 class="mt-3 text-2xl font-bold leading-tight text-gray-900 sm:text-3xl">{{ $document->title }}</h1>

                @if ($document->description)
                    <p class="mt-4 leading-relaxed text-gray-700">{{ $document->description }}</p>
                @endif

                {{-- Preview --}}
                <div class="mt-8">
                    @if ($document->mime_type === 'application/pdf')
                        <div id="pdf-container"
                             data-url="{{ route('documents.preview', $document) }}"
                             class="space-y-3 rounded-xl border border-gray-200 bg-gray-100 p-3">
                            <p id="pdf-status" class="py-10 text-center text-sm text-gray-500">Memuat pratinjau dokumen…</p>
                        </div>
                    @elseif (str_starts_with((string) $document->mime_type, 'image/'))
                        <img src="{{ route('documents.preview', $document) }}" alt="{{ $document->title }}"
                             class="max-h-[36rem] w-auto rounded-xl border border-gray-200 shadow-sm">
                    @else
                        <div class="rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500">
                            Pratinjau tidak tersedia untuk jenis berkas ini. Silakan unduh dokumen.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Sidebar metadata --}}
            <aside>
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <a href="{{ route('documents.download', $document) }}"
                       class="flex w-full items-center justify-center gap-2 rounded-lg bg-unm-500 px-4 py-3 font-semibold text-white transition hover:bg-unm-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        Unduh Dokumen
                    </a>

                    <dl class="mt-6 space-y-4 text-sm">
                        <div>
                            <dt class="font-medium text-gray-500">Nama berkas</dt>
                            <dd class="mt-1 break-words text-gray-900">{{ $document->file_name }}</dd>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="font-medium text-gray-500">Ukuran</dt>
                                <dd class="mt-1 text-gray-900">{{ $document->file_size ? Number::fileSize($document->file_size) : '—' }}</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500">Jenis</dt>
                                <dd class="mt-1 text-gray-900">{{ strtoupper(pathinfo($document->file_name, PATHINFO_EXTENSION)) ?: $document->mime_type }}</dd>
                            </div>
                        </div>
                        @if ($document->academic_year)
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <dt class="font-medium text-gray-500">Tahun akademik</dt>
                                    <dd class="mt-1 text-gray-900">{{ $document->academic_year }}</dd>
                                </div>
                                @if ($document->semester && $document->semester !== '-')
                                    <div>
                                        <dt class="font-medium text-gray-500">Semester</dt>
                                        <dd class="mt-1 capitalize text-gray-900">{{ $document->semester }}</dd>
                                    </div>
                                @endif
                            </div>
                        @endif
                        @if ($document->course_name)
                            <div>
                                <dt class="font-medium text-gray-500">Mata kuliah</dt>
                                <dd class="mt-1 text-gray-900">{{ $document->course_name }}</dd>
                            </div>
                        @endif
                        @if ($document->lecturer_name)
                            <div>
                                <dt class="font-medium text-gray-500">Dosen</dt>
                                <dd class="mt-1 text-gray-900">{{ $document->lecturer_name }}</dd>
                            </div>
                        @endif
                        <div class="grid grid-cols-2 gap-4 border-t border-gray-100 pt-4">
                            <div>
                                <dt class="font-medium text-gray-500">Dilihat</dt>
                                <dd class="mt-1 text-gray-900">{{ number_format($document->view_count, 0, ',', '.') }}×</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500">Diunduh</dt>
                                <dd class="mt-1 text-gray-900">{{ number_format($document->download_count, 0, ',', '.') }}×</dd>
                            </div>
                        </div>
                        <div class="border-t border-gray-100 pt-4">
                            <dt class="font-medium text-gray-500">Diunggah</dt>
                            <dd class="mt-1 text-gray-900">{{ $document->created_at->translatedFormat('d F Y') }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Dokumen terkait --}}
                @if ($relatedDocuments->isNotEmpty())
                    <div class="mt-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h2 class="text-sm font-semibold uppercase tracking-wider text-gray-500">Dokumen Terkait</h2>
                        <ul class="mt-4 space-y-3">
                            @foreach ($relatedDocuments as $related)
                                <li>
                                    <a href="{{ route('documents.show', $related) }}"
                                       class="text-sm font-medium text-gray-800 hover:text-unm-600">
                                        {{ $related->title }}
                                    </a>
                                    <p class="text-xs text-gray-500">{{ $related->created_at->translatedFormat('d F Y') }}</p>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </aside>
        </div>
    </section>

    @if ($document->mime_type === 'application/pdf')
        {{-- Preview PDF dengan PDF.js (dibundel lokal, tanpa CDN) --}}
        @vite('resources/js/pdf-viewer.js')
    @endif
@endsection
