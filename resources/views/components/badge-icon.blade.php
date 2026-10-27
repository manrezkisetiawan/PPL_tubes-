@props(['badge', 'locked' => false, 'size' => 'md'])

@php
    $dimension = [
        'sm' => 'h-10 w-10 text-xl',
        'md' => 'h-14 w-14 text-3xl',
        'lg' => 'h-20 w-20 text-4xl',
    ][$size];
@endphp

<div {{ $attributes->merge(['class' => "relative flex {$dimension} shrink-0 items-center justify-center rounded-full ".($locked ? 'bg-gray-100' : 'bg-amber-50 ring-2 ring-amber-300')]) }}>
    @if ($badge->icon_url)
        <img src="{{ $badge->icon_url }}" alt="" class="h-full w-full rounded-full object-cover {{ $locked ? 'opacity-40 grayscale' : '' }}">
    @else
        <span aria-hidden="true" class="{{ $locked ? 'opacity-40 grayscale' : '' }}">{{ $badge->default_icon }}</span>
    @endif

    @if ($locked)
        <span class="absolute -bottom-1 -right-1 rounded-full bg-white px-0.5 text-xs shadow" title="Terkunci">🔒</span>
    @endif
</div>
