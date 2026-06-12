<x-filament-panels::page>
    <form wire:submit="import" class="space-y-6">
        {{ $this->form }}

        <x-filament::button type="submit" icon="heroicon-o-user-plus">
            Impor Pengguna
        </x-filament::button>
    </form>

    @if ($importResult !== null)
        <x-filament::section heading="Hasil Impor">
            <p class="text-sm">
                <strong>{{ $importResult['created'] }}</strong> akun dibuat,
                <strong>{{ count($importResult['skipped']) }}</strong> baris dilewati.
            </p>

            @if (count($importResult['skipped']) > 0)
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-left">
                                <th class="py-2 pr-6 font-semibold">Baris</th>
                                <th class="py-2 font-semibold">Alasan dilewati</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($importResult['skipped'] as $skip)
                                <tr class="border-b border-gray-100">
                                    <td class="py-2 pr-6">{{ $skip['row'] }}</td>
                                    <td class="py-2">{{ $skip['reason'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    @endif
</x-filament-panels::page>
