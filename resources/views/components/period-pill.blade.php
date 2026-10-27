@props(['challenge'])

@php
    $classes = [
        'berjalan' => 'bg-emerald-100 text-emerald-800',
        'akan_datang' => 'bg-indigo-100 text-indigo-800',
        'berakhir' => 'bg-gray-100 text-gray-700',
    ][$challenge->period];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {$classes}"]) }}>
    {{ $challenge->period_label }}
</span>
