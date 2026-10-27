@props(['status', 'prefix' => null])

@php
    $classes = [
        'berjalan' => 'bg-sky-100 text-sky-800',
        'selesai' => 'bg-emerald-100 text-emerald-800',
        'gagal' => 'bg-red-100 text-red-800',
        'batal' => 'bg-gray-100 text-gray-700',
    ][$status] ?? 'bg-gray-100 text-gray-700';
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {$classes}"]) }}>
    {{ $prefix }}{{ \App\Models\ChallengeUser::STATUSES[$status] ?? $status }}
</span>
