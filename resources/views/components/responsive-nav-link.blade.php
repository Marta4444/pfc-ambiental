@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-eco-500 text-start text-base font-medium text-eco-700 bg-eco-50 focus:outline-none focus:text-eco-800 focus:bg-eco-100 focus:border-eco-700 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-eco-700 hover:bg-eco-50 hover:border-eco-300 focus:outline-none focus:text-eco-700 focus:bg-eco-50 focus:border-eco-300 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
