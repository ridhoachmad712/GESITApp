<section class="mx-auto max-w-7xl px-4 pt-14 sm:px-6 lg:px-8">
    <div>
        <h2 class="text-xl font-bold text-gray-900 sm:text-2xl">{{ \App\Models\Setting::get('section_categories_title') }}</h2>
        @if (\App\Models\Setting::get('section_categories_subtitle'))
            <p class="mt-1 text-sm text-gray-600">
                {{ str_replace('{jumlah}', $totalCategories, \App\Models\Setting::get('section_categories_subtitle')) }}
            </p>
        @endif
    </div>

    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($categories as $category)
            @php
                try {
                    $iconHtml = svg($category->icon ?: 'heroicon-o-folder', 'h-7 w-7')->toHtml();
                } catch (\Throwable) {
                    $iconHtml = svg('heroicon-o-folder', 'h-7 w-7')->toHtml();
                }
                $isEmpty = $category->visible_documents_count === 0;
            @endphp
            <div class="flex h-full gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition {{ $isEmpty ? 'opacity-60' : 'hover:border-unm-300 hover:shadow-md' }}">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg {{ $isEmpty ? 'bg-gray-100 text-gray-400' : 'bg-unm-50 text-unm-600' }}">
                    {!! $iconHtml !!}
                </span>
                <div>
                    <h3 class="font-semibold {{ $isEmpty ? 'text-gray-600' : 'text-gray-900' }}">
                        @if (Route::has('arsip.show'))
                            <a href="{{ route('arsip.show', $category) }}" class="hover:text-unm-600">{{ $category->name }}</a>
                        @else
                            {{ $category->name }}
                        @endif
                    </h3>
                    @if ($category->description)
                        <p class="mt-1 line-clamp-2 text-sm text-gray-600">{{ $category->description }}</p>
                    @endif
                    @if ($isEmpty)
                        <p class="mt-2 text-xs font-medium text-gray-400">Belum ada dokumen publik</p>
                    @else
                        <p class="mt-2 text-xs font-medium text-unm-600">
                            {{ number_format($category->visible_documents_count, 0, ',', '.') }} dokumen publik
                        </p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</section>
