@props(['document'])

<article class="flex h-full flex-col rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:border-unm-300 hover:shadow-md">
    <div class="flex items-center gap-2 text-xs">
        <span class="rounded-full bg-unm-50 px-2.5 py-1 font-medium text-unm-700">
            {{ $document->category->name }}
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
        <a href="{{ route('documents.download', $document) }}"
           class="inline-flex items-center gap-1 font-semibold text-unm-600 hover:text-unm-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Unduh
        </a>
    </div>
</article>
