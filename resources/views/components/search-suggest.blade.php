@props([
    'kategori' => null,
    'placeholder' => 'Cari judul dokumen…',
    'value' => '',
    'inputClass' => 'w-full border-0 px-5 py-3 text-gray-900 placeholder-gray-400 focus:ring-0',
])

<div class="relative w-full"
     x-data="searchSuggest(@js(route('cari.saran')), @js($kategori), @js($value))"
     @click.outside="close()"
     @keydown.escape="close()">
    <input type="search" name="q" placeholder="{{ $placeholder }}"
           x-model="q" @input="fetchItems()" @focus="items.length && (open = true)"
           autocomplete="off"
           {{ $attributes->merge(['class' => $inputClass]) }}>

    <div x-show="open" x-cloak
         class="absolute left-0 right-0 top-full z-30 mt-1.5 overflow-hidden rounded-xl border border-gray-200 bg-white text-left shadow-lg">
        <template x-for="item in items" :key="item.url">
            <a :href="item.url" class="block px-4 py-2.5 hover:bg-unm-50">
                <span x-text="item.title" class="block text-sm font-medium text-gray-900"></span>
                <span x-text="item.category" class="block text-xs text-gray-500"></span>
            </a>
        </template>
    </div>
</div>
