@extends('layouts.public')

@section('title', 'Kerja Sama')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <nav class="text-sm text-gray-500">
            <a href="{{ route('home') }}" class="hover:text-unm-600">Beranda</a>
            <span class="mx-2">/</span>
            <span class="font-medium text-gray-900">Kerja Sama</span>
        </nav>

        <h1 class="mt-4 text-2xl font-bold text-gray-900 sm:text-3xl">Kerja Sama</h1>
        <p class="mt-2 max-w-2xl text-sm text-gray-600">
            Daftar nota kesepahaman (MoU), perjanjian kerja sama (MoA), dan implementation arrangement (IA)
            Program Studi Manajemen FEB UNM dengan berbagai mitra.
        </p>

        @if ($agreements->isEmpty())
            <div class="mt-8 rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center text-sm text-gray-500">
                Belum ada data kerja sama.
            </div>
        @else
            <div class="mt-8 overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <tr>
                            <th class="px-5 py-3">Judul</th>
                            <th class="px-5 py-3">Mitra</th>
                            <th class="px-5 py-3">Jenis</th>
                            <th class="px-5 py-3">Masa Berlaku</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($agreements as $agreement)
                            <tr class="hover:bg-gray-50">
                                <td class="max-w-xs px-5 py-4 font-medium text-gray-900">{{ $agreement->title }}</td>
                                <td class="px-5 py-4 text-gray-700">{{ $agreement->partner_name }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">
                                        {{ $agreement->type }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-gray-700">
                                    @if ($agreement->start_date || $agreement->end_date)
                                        {{ $agreement->start_date?->translatedFormat('d M Y') ?? '…' }}
                                        –
                                        {{ $agreement->end_date?->translatedFormat('d M Y') ?? 'tanpa batas' }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if ($agreement->isActive())
                                        <span class="rounded-full bg-green-50 px-2.5 py-1 text-xs font-medium text-green-700">Aktif</span>
                                    @else
                                        <span class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-medium text-red-700">Kedaluwarsa</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $agreements->links() }}
            </div>
        @endif

        <p class="mt-6 text-xs text-gray-400">
            File perjanjian lengkap bersifat internal dan hanya dapat diakses oleh dosen serta pengelola prodi.
        </p>
    </section>
@endsection
