@extends('layouts.public')

@section('content')

    {{-- Hero — foto latar + overlay warna dari Pengaturan Tampilan,
         fallback gradasi warna tema bila foto belum diunggah --}}
    @php
        $heroImage = \App\Models\Setting::get('hero_image_path');
        [$ovR, $ovG, $ovB] = \App\Support\ColorPalette::hexToRgb(\App\Models\Setting::get('hero_overlay_color') ?? '#1E3A8A');
        $ovOpacity = max(0, min(100, (int) \App\Models\Setting::get('hero_overlay_opacity'))) / 100;

        $heroTitle = \App\Models\Setting::get('hero_title')
            ?: \App\Models\Setting::get('site_name').' — '.\App\Models\Setting::get('site_tagline');
        $heroChips = json_decode(\App\Models\Setting::get('hero_search_chips') ?? '[]', true) ?: [];
    @endphp
    <section class="relative text-white {{ $heroImage ? 'bg-cover bg-center' : 'bg-gradient-to-br from-unm-500 via-unm-600 to-unm-700' }}"
             @if ($heroImage) style="background-image: url('{{ Storage::disk('public')->url($heroImage) }}')" @endif>
        @if ($heroImage)
            <div class="absolute inset-0" style="background-color: rgb({{ $ovR }} {{ $ovG }} {{ $ovB }} / {{ $ovOpacity }})" aria-hidden="true"></div>
        @endif
        <div class="relative mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
            <div class="max-w-3xl">
                <p class="text-sm font-semibold uppercase tracking-widest text-unm-100">
                    {{ \App\Models\Setting::get('site_owner') }}
                </p>
                <h1 class="mt-3 text-3xl font-bold leading-tight sm:text-5xl">
                    {{ $heroTitle }}
                </h1>
                <p class="mt-5 max-w-2xl text-base leading-relaxed text-unm-50 sm:text-lg">
                    {{ \App\Models\Setting::get('hero_description') }}
                </p>

                @if (Route::has('cari'))
                    <form action="{{ route('cari') }}" method="GET" class="mt-8 flex max-w-xl rounded-xl bg-white shadow-lg">
                        <x-search-suggest :placeholder="\App\Models\Setting::get('hero_search_placeholder')"
                                          input-class="w-full rounded-l-xl border-0 px-5 py-3.5 text-gray-900 placeholder-gray-400 focus:ring-0" />
                        <button type="submit" class="rounded-r-xl bg-unm-800 px-6 text-sm font-semibold text-white transition hover:bg-unm-900">
                            Cari
                        </button>
                    </form>

                    {{-- Pencarian populer: satu klik tanpa mengetik --}}
                    @if (count($heroChips) > 0)
                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <span class="text-xs font-medium text-unm-100">Populer:</span>
                            @foreach ($heroChips as $keyword)
                                <a href="{{ route('cari', ['q' => $keyword]) }}"
                                   class="rounded-full bg-white/15 px-3 py-1 text-xs font-medium text-white transition hover:bg-white/30">
                                    {{ $keyword }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                @endif

                <div class="mt-10 flex flex-wrap gap-8">
                    <div>
                        <div class="text-3xl font-bold">{{ number_format($totalPublicDocuments, 0, ',', '.') }}</div>
                        <div class="mt-1 text-sm text-unm-100">Dokumen publik</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold">{{ $totalCategories }}</div>
                        <div class="mt-1 text-sm text-unm-100">Kategori arsip</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Ajakan masuk: publik tidak tahu ada dokumen di balik login --}}
    @guest
        @if (\App\Models\Setting::get('login_banner_enabled') === '1')
            <section class="border-b border-unm-100 bg-unm-50">
                <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-3.5 sm:px-6 lg:px-8">
                    <p class="text-sm text-unm-900">
                        {{ \App\Models\Setting::get('login_banner_text') }}
                    </p>
                    <a href="{{ route('login') }}"
                       class="shrink-0 rounded-lg bg-unm-500 px-4 py-1.5 text-sm font-semibold text-white transition hover:bg-unm-600">
                        {{ \App\Models\Setting::get('login_banner_button') }}
                    </a>
                </div>
            </section>
        @endif
    @endguest

    {{-- Seksi beranda — urutan & tampil/sembunyi diatur dari Konten Beranda --}}
    @php
        $homeSections = collect(['featured', 'categories', 'popular', 'latest'])
            ->filter(fn (string $key): bool => \App\Models\Setting::get("section_{$key}_visible") === '1')
            ->sortBy(fn (string $key): int => (int) \App\Models\Setting::get("section_{$key}_order"))
            ->values();
    @endphp
    @foreach ($homeSections as $sectionKey)
        @include('partials.home.'.$sectionKey)
    @endforeach

    <div class="pb-14"></div>

@endsection
