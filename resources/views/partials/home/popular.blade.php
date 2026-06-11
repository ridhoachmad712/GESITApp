@if ($popularDocuments->isNotEmpty())
    <section class="mx-auto max-w-7xl px-4 pt-14 sm:px-6 lg:px-8">
        <h2 class="text-xl font-bold text-gray-900 sm:text-2xl">{{ \App\Models\Setting::get('section_popular_title') }}</h2>
        @if (\App\Models\Setting::get('section_popular_subtitle'))
            <p class="mt-1 text-sm text-gray-600">{{ \App\Models\Setting::get('section_popular_subtitle') }}</p>
        @endif
        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($popularDocuments as $document)
                <x-document-card :document="$document" />
            @endforeach
        </div>
    </section>
@endif
