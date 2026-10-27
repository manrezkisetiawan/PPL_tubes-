@props(['value' => 0, 'tone' => 'emerald'])

@php
    $bar = [
        'emerald' => 'bg-emerald-500',
        'amber' => 'bg-amber-500',
        'red' => 'bg-red-400',
        'gray' => 'bg-gray-400',
    ][$tone] ?? 'bg-emerald-500';
    $percent = max(0, min(100, (float) $value));
@endphp

<div {{ $attributes->merge(['class' => 'h-2.5 w-full overflow-hidden rounded-full bg-gray-100']) }}
     role="progressbar" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
    <div class="h-full rounded-full {{ $bar }}" style="width: {{ $percent }}%"></div>
</div>
