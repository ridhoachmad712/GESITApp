@props(['active' => false])

<a {{ $attributes->merge([
    'class' => 'rounded-md px-3 py-2 text-sm font-medium transition '.(
        $active
            ? 'bg-unm-50 text-unm-700'
            : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
    ),
]) }}>{{ $slot }}</a>
