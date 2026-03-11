@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-eco-500 focus:ring-eco-500 rounded-md shadow-sm']) }}>
