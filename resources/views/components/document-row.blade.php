@props(['document'])

<div class="flex flex-col gap-2 px-5 py-4 transition hover:bg-gray-50 sm:flex-row sm:items-center sm:justify-between">
    <div class="min-w-0">
        <a href="{{ route('documents.show', $document) }}"
           class="font-medium text-gray-900 hover:text-unm-600">
            {{ $document->title }}
        </a>
        <p class="mt-0.5 flex flex-wrap items-center gap-x-2 text-xs text-gray-500">
            <span class="text-unm-700">{{ $document->category->name }}</span>
            @if ($document->academic_year)
                <span>· {{ $document->academic_year }}</span>
            @endif
            <span>· {{ $document->created_at->translatedFormat('d M Y') }}</span>
            @if ($document->isExternal())
                <span>· Tautan eksternal</span>
            @elseif ($document->file_size)
                <span>· {{ Number::fileSize($document->file_size) }}</span>
            @endif
        </p>
    </div>

    <div class="flex shrink-0 items-center gap-3 text-xs">
        @if ($document->isExternal())
            <a href="{{ route('documents.download', $document) }}" target="_blank" rel="noopener"
               class="font-semibold text-unm-600 hover:text-unm-700">Buka ↗</a>
        @else
            <a href="{{ route('documents.preview', $document) }}" target="_blank" rel="noopener"
               class="font-semibold text-gray-500 hover:text-unm-700">Lihat</a>
            <a href="{{ route('documents.download', $document) }}"
               class="font-semibold text-unm-600 hover:text-unm-700">Unduh</a>
        @endif
    </div>
</div>
