@props(['href', 'variant' => 'primary'])

@php
    $classes = [
        'primary' => 'border-transparent bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-500',
        'secondary' => 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:ring-emerald-500',
    ][$variant];
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => "inline-flex items-center justify-center rounded-md border px-4 py-2 text-xs font-semibold uppercase tracking-widest shadow-sm transition focus:outline-none focus:ring-2 focus:ring-offset-2 {$classes}"]) }}>
    {{ $slot }}
</a>
