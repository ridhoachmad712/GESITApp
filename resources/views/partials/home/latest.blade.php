<section class="mx-auto max-w-7xl px-4 pt-14 sm:px-6 lg:px-8">
    <h2 class="text-xl font-bold text-gray-900 sm:text-2xl">{{ \App\Models\Setting::get('section_latest_title') }}</h2>
    @if (\App\Models\Setting::get('section_latest_subtitle'))
        <p class="mt-1 text-sm text-gray-600">{{ \App\Models\Setting::get('section_latest_subtitle') }}</p>
    @endif

    @if ($latestDocuments->isEmpty())
        <div class="mt-6 rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center text-sm text-gray-500">
            Belum ada dokumen publik yang diterbitkan.
        </div>
    @else
        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($latestDocuments as $document)
                <x-document-card :document="$document" />
            @endforeach
        </div>
    @endif
</section>
