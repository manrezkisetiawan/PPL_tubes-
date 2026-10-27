<x-app-layout>
    <x-slot name="title">Lencana</x-slot>
    <x-slot name="header">
        <x-page-header title="Koleksi Lencana" subtitle="{{ $earned->count() }} dari {{ $badges->count() }} lencana sudah kamu raih." />
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if ($newBadges->isNotEmpty())
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="status">
                    🎉 Lencana baru diraih: <strong>{{ $newBadges->pluck('name')->join(', ', ' dan ') }}</strong>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($badges as $badge)
                    @php
                        $isEarned = $earned->has($badge->id);
                        $current = $metrics[$badge->type] ?? 0;
                        $percent = $isEarned ? 100 : min(100, $current / max((float) $badge->required_value, 1) * 100);
                    @endphp
                    <article class="flex gap-4 rounded-lg bg-white p-5 shadow-sm {{ $isEarned ? '' : 'opacity-90' }}">
                        <x-badge-icon :badge="$badge" :locked="! $isEarned" />
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="font-semibold text-gray-800">{{ $badge->name }}</h3>
                                @if ($isEarned)
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">Terbuka</span>
                                @else
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">Terkunci</span>
                                @endif
                            </div>
                            @if ($badge->description)
                                <p class="mt-1 text-sm text-gray-600">{{ $badge->description }}</p>
                            @endif
                            <p class="mt-2 text-xs font-medium text-gray-500">Syarat: {{ $badge->requirement_text }}</p>

                            @if ($isEarned)
                                <p class="mt-2 text-xs text-emerald-700">Diraih {{ $earned[$badge->id]->pivot->earned_at->translatedFormat('d M Y') }}</p>
                            @else
                                <x-progress-bar class="mt-2" :value="$percent" tone="amber" />
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ format_number(min($current, (float) $badge->required_value), 0) }}
                                    / {{ format_number($badge->required_value, 0) }}
                                </p>
                            @endif
                        </div>
                    </article>
                @empty
                    <p class="text-sm text-gray-500">Belum ada lencana yang tersedia.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
