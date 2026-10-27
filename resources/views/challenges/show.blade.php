<x-app-layout>
    <x-slot name="title">{{ $challenge->title }}</x-slot>
    <x-slot name="header">
        <x-page-header :title="$challenge->title" :subtitle="$challenge->category->icon.' '.$challenge->category->name">
            <x-slot name="actions">
                <x-button-link :href="route('challenges.index')" variant="secondary">Kembali</x-button-link>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto grid max-w-7xl grid-cols-1 gap-6 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
            <section class="rounded-lg bg-white p-6 shadow-sm lg:col-span-2">
                <div class="flex flex-wrap items-center gap-2">
                    <x-period-pill :challenge="$challenge" />
                    @if ($participation)
                        <x-status-pill :status="$participation->status" prefix="Kamu: " />
                    @endif
                </div>

                @if ($challenge->description)
                    <p class="mt-4 text-gray-700">{{ $challenge->description }}</p>
                @endif

                <dl class="mt-6 grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
                    <div>
                        <dt class="text-gray-500">Mulai</dt>
                        <dd class="font-medium text-gray-800">{{ $challenge->start_date->translatedFormat('d M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Selesai</dt>
                        <dd class="font-medium text-gray-800">{{ $challenge->end_date->translatedFormat('d M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Target hemat</dt>
                        <dd class="font-medium text-gray-800">{{ format_kg($challenge->reduction_target) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Hadiah</dt>
                        <dd class="font-medium text-amber-700">⭐ {{ format_number($challenge->reward_points, 0) }}</dd>
                    </div>
                </dl>

                <div class="mt-6 rounded-lg bg-gray-50 p-4 text-sm text-gray-600">
                    <p class="font-medium text-gray-800">Bagaimana progres dihitung?</p>
                    <p class="mt-1">
                        Garis dasarmu adalah rata-rata emisi harian kategori {{ $challenge->category->name }} selama
                        {{ $baselineDays }} hari sebelum kamu mulai. Setiap hari kamu mencatat aktivitas selama tantangan,
                        selisih antara garis dasar dan emisi {{ strtolower($challenge->category->name) }} hari itu dihitung
                        sebagai emisi yang dihemat. Tantangan selesai ketika total yang dihemat mencapai target, dan gagal bila
                        periode berakhir sebelum target tercapai.
                    </p>
                </div>
            </section>

            <aside class="space-y-6">
                <section class="rounded-lg bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-800">Keikutsertaanmu</h3>

                    @if ($participation && $participation->status !== 'batal')
                        <x-progress-bar class="mt-4" :value="$participation->progress"
                                        :tone="$participation->status === 'gagal' ? 'red' : 'emerald'" />
                        <p class="mt-1 text-sm text-gray-600">{{ format_number($participation->progress) }}% tercapai</p>
                        <p class="mt-1 text-xs text-gray-500">Bergabung {{ $participation->joined_at->translatedFormat('d M Y') }}</p>

                        @if ($breakdown)
                            <dl class="mt-4 space-y-2 text-sm">
                                <div class="flex justify-between gap-3">
                                    <dt class="text-gray-500">Garis dasar harian</dt>
                                    <dd class="text-right font-medium text-gray-800">
                                        {{ $breakdown['baseline_daily'] === null ? 'Belum ada data' : format_kg($breakdown['baseline_daily']) }}
                                    </dd>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <dt class="text-gray-500">Hari tercatat</dt>
                                    <dd class="font-medium text-gray-800">{{ $breakdown['logged_days'] }} hari</dd>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <dt class="text-gray-500">Emisi kategori</dt>
                                    <dd class="font-medium text-gray-800">{{ format_kg($breakdown['actual_emission']) }}</dd>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <dt class="text-gray-500">Sudah dihemat</dt>
                                    <dd class="font-semibold text-emerald-700">{{ format_kg($breakdown['saved']) }}</dd>
                                </div>
                            </dl>

                            @if ($breakdown['baseline_daily'] === null)
                                <p class="mt-3 rounded-md bg-amber-50 p-3 text-xs text-amber-800">
                                    Belum ada catatan aktivitas dalam {{ $baselineDays }} hari sebelum tantangan, sehingga garis dasar belum dapat dihitung dan progres tetap 0%.
                                </p>
                            @endif

                            <div class="mt-5 flex flex-wrap gap-2">
                                <form method="POST" action="{{ route('participations.update', $challenge) }}">
                                    @csrf
                                    @method('PATCH')
                                    <x-primary-button>Perbarui progres</x-primary-button>
                                </form>
                                <form method="POST" action="{{ route('participations.destroy', $challenge) }}"
                                      onsubmit="return confirm('Yakin mengundurkan diri? Progres tidak akan dihitung lagi.')">
                                    @csrf
                                    @method('DELETE')
                                    <x-secondary-button type="submit">Mundur</x-secondary-button>
                                </form>
                            </div>
                        @endif
                    @elseif ($canJoin)
                        <p class="mt-2 text-sm text-gray-600">
                            {{ $participation ? 'Kamu pernah mengundurkan diri dari tantangan ini. Ikuti lagi untuk memulai dari awal.' : 'Kamu belum mengikuti tantangan ini.' }}
                        </p>
                        <form method="POST" action="{{ route('participations.store', $challenge) }}" class="mt-4">
                            @csrf
                            <x-primary-button>{{ $participation ? 'Ikuti lagi' : 'Ikuti tantangan' }}</x-primary-button>
                        </form>
                    @else
                        <p class="mt-2 text-sm text-gray-600">Tantangan ini sudah berakhir dan tidak dapat diikuti lagi.</p>
                    @endif
                </section>

                <section class="rounded-lg bg-white p-6 text-sm text-gray-600 shadow-sm">
                    <p><span class="font-semibold text-gray-800">{{ $challenge->active_participants_count }}</span> pengguna mengikuti tantangan ini.</p>
                </section>
            </aside>
        </div>
    </div>
</x-app-layout>
