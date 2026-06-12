@extends('layouts.public')

@section('title', $document->title)
@section('meta_description', Str::limit((string) $document->description, 150))
@section('og_type', 'article')

@push('head')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'CreativeWork',
            'name' => $document->title,
            'description' => Str::limit((string) $document->description, 200),
            'url' => route('documents.show', $document),
            'datePublished' => $document->created_at->toAtomString(),
            'dateModified' => $document->updated_at->toAtomString(),
            'inLanguage' => 'id',
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

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
                    @if ($document->isExternal())
                        @if ($embedUrl = $document->googleDriveEmbedUrl())
                            <iframe src="{{ $embedUrl }}"
                                    title="Pratinjau {{ $document->title }}"
                                    class="aspect-[4/3] w-full rounded-xl border border-gray-200 bg-gray-100 shadow-sm sm:aspect-video"
                                    allow="autoplay" loading="lazy"></iframe>
                            <p class="mt-2 text-xs text-gray-400">Pratinjau dimuat dari Google Drive.</p>
                        @else
                            <div class="rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500">
                                Dokumen ini tersimpan di penyimpanan eksternal.
                                <a href="{{ route('documents.preview', $document) }}" target="_blank" rel="noopener"
                                   class="font-semibold text-unm-600 hover:text-unm-700">Buka dokumen ↗</a>
                            </div>
                        @endif
                    @elseif ($document->mime_type === 'application/pdf')
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
                    @if ($document->isExternal())
                        <a href="{{ route('documents.download', $document) }}" target="_blank" rel="noopener"
                           class="flex w-full items-center justify-center gap-2 rounded-lg bg-unm-500 px-4 py-3 font-semibold text-white transition hover:bg-unm-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                            Buka Dokumen
                        </a>
                    @else
                        <a href="{{ route('documents.download', $document) }}"
                           class="flex w-full items-center justify-center gap-2 rounded-lg bg-unm-500 px-4 py-3 font-semibold text-white transition hover:bg-unm-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            Unduh Dokumen
                        </a>
                        <a href="{{ route('documents.preview', $document) }}" target="_blank" rel="noopener"
                           class="mt-3 flex w-full items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-3 font-semibold text-gray-700 transition hover:border-unm-400 hover:text-unm-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Lihat Dokumen
                        </a>
                    @endif

                    {{-- Bagikan --}}
                    <div class="mt-3 grid grid-cols-2 gap-2" x-data="{ copied: false }">
                        <button type="button"
                                @click="navigator.clipboard.writeText(@js(route('documents.show', $document))).then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                                class="flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 transition hover:border-unm-400 hover:text-unm-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
                            <span x-show="!copied">Salin Tautan</span>
                            <span x-show="copied" x-cloak class="text-green-600">Tersalin ✓</span>
                        </button>
                        <a href="https://wa.me/?text={{ urlencode($document->title.' — '.route('documents.show', $document)) }}"
                           target="_blank" rel="noopener"
                           class="flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 transition hover:border-green-500 hover:text-green-700">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            WhatsApp
                        </a>
                    </div>

                    <dl class="mt-6 space-y-4 text-sm">
                        @if ($document->isExternal())
                            <div>
                                <dt class="font-medium text-gray-500">Penyimpanan</dt>
                                <dd class="mt-1 text-gray-900">Tautan eksternal{{ $document->googleDriveEmbedUrl() ? ' (Google Drive)' : '' }}</dd>
                            </div>
                        @else
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
                                    <dd class="mt-1 text-gray-900">{{ strtoupper(pathinfo((string) $document->file_name, PATHINFO_EXTENSION)) ?: $document->mime_type }}</dd>
                                </div>
                            </div>
                        @endif
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
