@php
    $participation = $participations[$challenge->id] ?? null;
@endphp

<article class="flex flex-col rounded-lg bg-white p-5 shadow-sm">
    <div class="flex flex-wrap items-center gap-2">
        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">
            {{ $challenge->category->icon }} {{ $challenge->category->name }}
        </span>
        <x-period-pill :challenge="$challenge" />
        @if ($participation)
            <x-status-pill :status="$participation->status" prefix="Kamu: " />
        @endif
    </div>

    <h3 class="mt-3 text-lg font-semibold text-gray-800">{{ $challenge->title }}</h3>
    @if ($challenge->description)
        <p class="mt-1 line-clamp-2 text-sm text-gray-600">{{ $challenge->description }}</p>
    @endif

    <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
        <div>
            <dt class="text-gray-500">Periode</dt>
            <dd class="font-medium text-gray-800">{{ $challenge->start_date->translatedFormat('d M') }} – {{ $challenge->end_date->translatedFormat('d M Y') }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">Target hemat</dt>
            <dd class="font-medium text-gray-800">{{ format_kg($challenge->reduction_target) }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">Hadiah</dt>
            <dd class="font-medium text-amber-700">⭐ {{ format_number($challenge->reward_points, 0) }} EcoPoint</dd>
        </div>
        <div>
            <dt class="text-gray-500">Peserta</dt>
            <dd class="font-medium text-gray-800">{{ $challenge->active_participants_count }} orang</dd>
        </div>
    </dl>

    <div class="mt-5 flex flex-1 items-end">
        <x-button-link :href="route('challenges.show', $challenge)" variant="secondary" class="w-full">Lihat detail</x-button-link>
    </div>
</article>
