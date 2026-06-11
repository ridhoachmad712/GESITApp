@props(['document'])

<article class="flex h-full flex-col rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-unm-300 hover:shadow-md">
    <div class="flex flex-wrap items-center gap-2 text-xs">
        <span class="rounded-full bg-unm-50 px-2.5 py-1 font-medium text-unm-700">
            {{ $document->category->name }}
        </span>
        @php
            $jenisBerkas = $document->isExternal()
                ? ($document->googleDriveEmbedUrl() ? 'Drive' : 'Tautan')
                : (strtoupper(pathinfo((string) $document->file_name, PATHINFO_EXTENSION)) ?: 'Berkas');
        @endphp
        <span class="rounded-full bg-gray-100 px-2.5 py-1 font-medium text-gray-600">
            {{ $jenisBerkas }}{{ ! $document->isExternal() && $document->file_size ? ' · '.Number::fileSize($document->file_size, 0) : '' }}
        </span>
        @if ($document->academic_year)
            <span class="text-gray-500">{{ $document->academic_year }}</span>
        @endif
    </div>

    <h3 class="mt-3 font-semibold leading-snug text-gray-900">
        <a href="{{ Route::has('documents.show') ? route('documents.show', $document) : route('documents.preview', $document) }}"
           class="hover:text-unm-600">
            {{ $document->title }}
        </a>
    </h3>

    @if ($document->description)
        <p class="mt-2 line-clamp-2 text-sm text-gray-600">{{ $document->description }}</p>
    @endif

    <div class="mt-auto flex items-center justify-between pt-4 text-xs text-gray-500">
        <span>{{ $document->created_at->translatedFormat('d F Y') }}</span>
        <span class="flex items-center gap-3">
            @if ($document->isExternal())
                <a href="{{ route('documents.download', $document) }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-1 font-semibold text-unm-600 hover:text-unm-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                    Buka
                </a>
            @else
                <a href="{{ route('documents.preview', $document) }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-1 font-semibold text-gray-500 hover:text-unm-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Lihat
                </a>
                <a href="{{ route('documents.download', $document) }}"
                   class="inline-flex items-center gap-1 font-semibold text-unm-600 hover:text-unm-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Unduh
                </a>
            @endif
        </span>
    </div>
</article>
