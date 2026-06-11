<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center rounded-lg border border-transparent bg-unm-500 px-4 py-2 text-sm font-semibold text-white transition duration-150 ease-in-out hover:bg-unm-600 focus:outline-none focus:ring-2 focus:ring-unm-500 focus:ring-offset-2 active:bg-unm-700']) }}>
    {{ $slot }}
</button>
