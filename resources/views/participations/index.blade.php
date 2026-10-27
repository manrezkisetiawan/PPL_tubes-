<x-app-layout>
    <x-slot name="title">Tantangan Saya</x-slot>
    <x-slot name="header">
        <x-page-header title="Tantangan Saya" subtitle="Pantau progres dan status setiap tantangan yang kamu ikuti.">
            <x-slot name="actions">
                <x-button-link :href="route('challenges.index')">Cari tantangan</x-button-link>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if ($justCompleted->isNotEmpty())
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="status">
                    🏆 Tantangan selesai: <strong>{{ $justCompleted->pluck('title')->join(', ', ' dan ') }}</strong>. EcoPoint sudah ditambahkan!
                </div>
            @endif

            <nav class="flex flex-wrap gap-2" aria-label="Saring status">
                @php
                    $tabs = ['' => 'Semua ('.$total.')'];
                    foreach (\App\Models\ChallengeUser::STATUSES as $key => $label) {
                        $tabs[$key] = $label.' ('.($statusCounts[$key] ?? 0).')';
                    }
                @endphp
                @foreach ($tabs as $key => $label)
                    <a href="{{ route('participations.index', $key ? ['status' => $key] : []) }}"
                       class="rounded-full px-3 py-1 text-sm font-medium {{ ($status ?? '') === $key ? 'bg-emerald-600 text-white' : 'bg-white text-gray-600 shadow-sm hover:bg-gray-50' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </nav>

            <div class="space-y-4">
                @forelse ($participations as $challenge)
                    @php($pivot = $challenge->pivot)
                    <article class="rounded-lg bg-white p-5 shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-status-pill :status="$pivot->status" />
                                    <span class="text-xs text-gray-500">{{ $challenge->category->icon }} {{ $challenge->category->name }}</span>
                                </div>
                                <h3 class="mt-2 text-lg font-semibold text-gray-800">
                                    <a href="{{ route('challenges.show', $challenge) }}" class="hover:underline">{{ $challenge->title }}</a>
                                </h3>
                                <p class="text-sm text-gray-500">
                                    {{ $challenge->start_date->translatedFormat('d M') }} – {{ $challenge->end_date->translatedFormat('d M Y') }}
                                    · bergabung {{ $pivot->joined_at->translatedFormat('d M Y') }}
                                </p>
                            </div>
                            <p class="text-sm font-medium text-amber-700">⭐ {{ format_number($challenge->reward_points, 0) }} EcoPoint</p>
                        </div>

                        <x-progress-bar class="mt-4" :value="$pivot->progress"
                                        :tone="match ($pivot->status) { 'gagal' => 'red', 'batal' => 'gray', default => 'emerald' }" />
                        <p class="mt-1 text-xs text-gray-500">{{ format_number($pivot->progress) }}% dari target hemat {{ format_kg($challenge->reduction_target) }}</p>

                        <div class="mt-4 flex flex-wrap gap-2">
                            @if ($pivot->status === 'berjalan')
                                <form method="POST" action="{{ route('participations.update', $challenge) }}">
                                    @csrf
                                    @method('PATCH')
                                    <x-primary-button>Perbarui progres</x-primary-button>
                                </form>
                                <form method="POST" action="{{ route('participations.destroy', $challenge) }}"
                                      onsubmit="return confirm('Yakin mengundurkan diri dari tantangan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-secondary-button type="submit">Mundur</x-secondary-button>
                                </form>
                            @elseif ($pivot->status === 'batal' && ! $challenge->hasEnded())
                                <form method="POST" action="{{ route('participations.store', $challenge) }}">
                                    @csrf
                                    <x-primary-button>Ikuti lagi</x-primary-button>
                                </form>
                            @endif
                            <x-button-link :href="route('challenges.show', $challenge)" variant="secondary">Detail</x-button-link>
                        </div>
                    </article>
                @empty
                    <div class="rounded-lg border border-dashed border-gray-300 bg-white p-8 text-center text-sm text-gray-500">
                        Belum ada tantangan{{ $status ? ' dengan status ini' : '' }}.
                        <a href="{{ route('challenges.index') }}" class="font-medium text-emerald-700 hover:underline">Lihat tantangan yang tersedia</a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
