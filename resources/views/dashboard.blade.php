<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>
    <x-slot name="header">
        <x-page-header title="Halo, {{ $user->name }} 👋" subtitle="Ringkasan jejak karbon dan pencapaianmu.">
            <x-slot name="actions">
                <x-button-link :href="route('activities.index')">+ Catat Aktivitas</x-button-link>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if ($user->isAdmin())
                <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-5 py-4">
                    <p class="text-sm text-emerald-900"><span class="font-semibold">Panel administrator:</span> kelola tantangan dan lencana untuk seluruh pengguna.</p>
                    <div class="flex flex-wrap gap-2">
                        <x-button-link :href="route('admin.challenges.index')" variant="secondary">Kelola Tantangan</x-button-link>
                        <x-button-link :href="route('admin.badges.index')" variant="secondary">Kelola Lencana</x-button-link>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <x-stat-card label="Emisi hari ini" :value="format_kg($emissionToday)" />
                <x-stat-card label="Emisi bulan ini" :value="format_kg($emissionMonth)" :hint="today()->translatedFormat('F Y')" />
                <x-stat-card label="EcoPoint" :value="'⭐ '.format_number($user->points, 0)" hint="Didapat dari menyelesaikan tantangan" />
                <x-stat-card label="Streak pencatatan" :value="'🔥 '.$user->streak_count.' hari'" hint="Hari berturut-turut mencatat aktivitas" />
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <section class="rounded-lg bg-white p-6 shadow-sm lg:col-span-2">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="text-lg font-semibold text-gray-800">Tantangan yang sedang kamu ikuti</h3>
                        <a href="{{ route('participations.index') }}" class="text-sm font-medium text-emerald-700 hover:underline">Lihat semua</a>
                    </div>

                    <div class="mt-4 space-y-4">
                        @forelse ($runningChallenges as $challenge)
                            <a href="{{ route('challenges.show', $challenge) }}" class="block rounded-lg border border-gray-100 p-4 hover:border-emerald-200 hover:bg-emerald-50/40">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="font-medium text-gray-800">{{ $challenge->category->icon }} {{ $challenge->title }}</p>
                                    <p class="text-sm text-gray-500">berakhir {{ $challenge->end_date->translatedFormat('d M Y') }}</p>
                                </div>
                                <x-progress-bar class="mt-3" :value="$challenge->pivot->progress" />
                                <p class="mt-1 text-xs text-gray-500">{{ format_number($challenge->pivot->progress) }}% dari target {{ format_kg($challenge->reduction_target) }}</p>
                            </a>
                        @empty
                            <div class="rounded-lg border border-dashed border-gray-200 p-6 text-center text-sm text-gray-500">
                                Kamu belum mengikuti tantangan.
                                <a href="{{ route('challenges.index') }}" class="font-medium text-emerald-700 hover:underline">Cari tantangan</a>
                            </div>
                        @endforelse
                    </div>
                </section>

                <section class="rounded-lg bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="text-lg font-semibold text-gray-800">Lencana</h3>
                        <a href="{{ route('badges.index') }}" class="text-sm font-medium text-emerald-700 hover:underline">{{ $badgeCount }}/{{ $badgeTotal }} diraih</a>
                    </div>

                    <div class="mt-4 space-y-3">
                        @forelse ($recentBadges as $badge)
                            <div class="flex items-center gap-3">
                                <x-badge-icon :badge="$badge" size="sm" />
                                <div>
                                    <p class="text-sm font-medium text-gray-800">{{ $badge->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $badge->pivot->earned_at->translatedFormat('d M Y') }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Belum ada lencana. Mulai catat aktivitas untuk meraih lencana pertamamu.</p>
                        @endforelse
                    </div>

                    <h3 class="mt-8 text-lg font-semibold text-gray-800">Riwayat EcoPoint</h3>
                    <ul class="mt-3 divide-y divide-gray-100">
                        @forelse ($pointLogs as $log)
                            <li class="flex items-start justify-between gap-3 py-2 text-sm">
                                <span class="text-gray-700">{{ $log->reason }}</span>
                                <span class="shrink-0 font-semibold text-emerald-700">+{{ format_number($log->points, 0) }}</span>
                            </li>
                        @empty
                            <li class="py-2 text-sm text-gray-500">Belum ada perolehan poin.</li>
                        @endforelse
                    </ul>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
