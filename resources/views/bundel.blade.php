<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Bundel Akreditasi LAMEMBA</h2>
            <p class="mt-1 text-sm text-gray-500">
                {{ number_format($totalEvidence, 0, ',', '.') }} bukti tertaut ke {{ $criteria->count() }} kriteria.
                Tandai dokumen sebagai bukti lewat panel admin → Dokumen → Metadata Akademik.
            </p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            @foreach ($criteria as $criterion)
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm"
                     x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }">
                    <button type="button" @click="open = !open"
                            class="flex w-full items-center justify-between gap-3 px-5 py-4 text-left transition hover:bg-gray-50">
                        <span class="flex items-center gap-3">
                            <span class="rounded-lg bg-unm-50 px-2.5 py-1 text-sm font-bold text-unm-700">{{ $criterion->code }}</span>
                            <span class="font-semibold text-gray-900">{{ $criterion->name }}</span>
                        </span>
                        <span class="flex items-center gap-3">
                            <span class="text-sm {{ $criterion->documents->isEmpty() ? 'font-medium text-red-500' : 'text-gray-500' }}">
                                {{ $criterion->documents->count() }} bukti
                            </span>
                            <svg class="h-5 w-5 text-gray-400 transition" :class="open && 'rotate-180'"
                                 fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                        </span>
                    </button>

                    <div x-show="open" x-cloak class="border-t border-gray-100">
                        @if ($criterion->documents->isEmpty())
                            <p class="px-5 py-6 text-sm text-gray-500">
                                Belum ada bukti untuk kriteria ini — tandai dokumen yang relevan di panel admin.
                            </p>
                        @else
                            <div class="divide-y divide-gray-100">
                                @foreach ($criterion->documents as $document)
                                    <div class="flex flex-col gap-2 px-5 py-3.5 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="min-w-0">
                                            <a href="{{ route('documents.show', $document) }}"
                                               class="font-medium text-gray-900 hover:text-unm-600">
                                                {{ $document->title }}
                                            </a>
                                            <p class="mt-0.5 text-xs text-gray-500">
                                                {{ $document->category->name }}
                                                @if ($document->academic_year) · {{ $document->academic_year }} @endif
                                                · {{ ucfirst($document->visibility) }}
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
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
