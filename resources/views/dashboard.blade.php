<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Dokumen untuk Anda</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Masuk sebagai <span class="font-medium capitalize">{{ auth()->user()->role }}</span> —
                    {{ auth()->user()->isMahasiswa()
                        ? 'Anda dapat mengakses dokumen publik dan khusus mahasiswa.'
                        : 'Anda dapat mengakses seluruh dokumen termasuk internal.' }}
                </p>
            </div>
            <a href="{{ route('home') }}" class="text-sm font-medium text-unm-600 hover:text-unm-700">
                ← Kembali ke beranda
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Filter & pencarian --}}
            <form method="GET" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
                    <div class="lg:col-span-2">
                        <label for="q" class="sr-only">Cari</label>
                        <input type="search" id="q" name="q" value="{{ $filters['q'] }}"
                               placeholder="Cari judul, deskripsi, mata kuliah, dosen…"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-unm-500 focus:ring-unm-500">
                    </div>
                    <div>
                        <label for="kategori" class="sr-only">Kategori</label>
                        <select id="kategori" name="kategori"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-unm-500 focus:ring-unm-500">
                            <option value="">Semua kategori</option>
                            @foreach ($categoryOptions as $key => $option)
                                @if (is_array($option))
                                    <optgroup label="{{ $key }}">
                                        @foreach ($option as $id => $name)
                                            <option value="{{ $id }}" @selected($filters['kategori'] === (string) $id)>{{ $name }}</option>
                                        @endforeach
                                    </optgroup>
                                @else
                                    <option value="{{ $key }}" @selected($filters['kategori'] === (string) $key)>{{ $option }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="tahun" class="sr-only">Tahun akademik</label>
                        <select id="tahun" name="tahun"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-unm-500 focus:ring-unm-500">
                            <option value="">Semua tahun</option>
                            @foreach ($years as $year)
                                <option value="{{ $year }}" @selected($filters['tahun'] === $year)>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <select name="semester" aria-label="Semester"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-unm-500 focus:ring-unm-500">
                            <option value="">Semua semester</option>
                            <option value="ganjil" @selected($filters['semester'] === 'ganjil')>Ganjil</option>
                            <option value="genap" @selected($filters['semester'] === 'genap')>Genap</option>
                        </select>
                        <button type="submit"
                                class="shrink-0 rounded-lg bg-unm-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-unm-600">
                            Terapkan
                        </button>
                    </div>
                </div>
                @if (array_filter($filters))
                    <div class="mt-3 text-right">
                        <a href="{{ route('dashboard') }}" class="text-sm text-unm-600 hover:underline">Hapus semua filter</a>
                    </div>
                @endif
            </form>

            {{-- Hasil --}}
            <div class="mt-6">
                <p class="text-sm text-gray-600">
                    {{ number_format($documents->total(), 0, ',', '.') }} dokumen ditemukan.
                </p>

                @if ($documents->isEmpty())
                    <div class="mt-4 rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center text-sm text-gray-500">
                        Tidak ada dokumen yang cocok dengan filter Anda.
                    </div>
                @else
                    <div class="mt-4 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($documents as $document)
                            <x-document-card :document="$document" />
                        @endforeach
                    </div>

                    <div class="mt-8">
                        {{ $documents->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
