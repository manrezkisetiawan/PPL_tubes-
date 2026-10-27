<x-app-layout>
    <x-slot name="title">{{ $challenge->title }}</x-slot>
    <x-slot name="header">
        <x-page-header :title="$challenge->title" :subtitle="$challenge->category->icon.' '.$challenge->category->name">
            <x-slot name="actions">
                <x-button-link :href="route('admin.challenges.index')" variant="secondary">Kembali</x-button-link>
                <x-button-link :href="route('admin.challenges.edit', $challenge)">Ubah</x-button-link>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="rounded-lg bg-white p-6 shadow-sm">
                <x-period-pill :challenge="$challenge" />
                @if ($challenge->description)
                    <p class="mt-3 text-gray-700">{{ $challenge->description }}</p>
                @endif

                <dl class="mt-6 grid grid-cols-2 gap-4 text-sm md:grid-cols-5">
                    <div>
                        <dt class="text-gray-500">Mulai</dt>
                        <dd class="font-medium text-gray-800">{{ $challenge->start_date->translatedFormat('d M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Selesai</dt>
                        <dd class="font-medium text-gray-800">{{ $challenge->end_date->translatedFormat('d M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Durasi</dt>
                        <dd class="font-medium text-gray-800">{{ $challenge->duration_days }} hari</dd>
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
            </section>

            <section class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 px-6 pt-6">
                    <h3 class="text-lg font-semibold text-gray-800">Peserta ({{ $participants->count() }})</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach (\App\Models\ChallengeUser::STATUSES as $key => $label)
                            <span class="text-xs text-gray-500"><x-status-pill :status="$key" /> {{ $statusCounts[$key] ?? 0 }}</span>
                        @endforeach
                    </div>
                </div>

                <div class="relative mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Pengguna</th>
                                <th class="px-6 py-3">Bergabung</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="w-1/3 px-6 py-3">Progres</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($participants as $participant)
                                <tr>
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-gray-800">{{ $participant->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $participant->email }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-gray-600">{{ $participant->pivot->joined_at->translatedFormat('d M Y') }}</td>
                                    <td class="px-6 py-4"><x-status-pill :status="$participant->pivot->status" /></td>
                                    <td class="px-6 py-4">
                                        <x-progress-bar :value="$participant->pivot->progress" />
                                        <p class="mt-1 text-xs text-gray-500">{{ format_number($participant->pivot->progress) }}%</p>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada peserta.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            @if ($challenge->isDeletable())
                <form method="POST" action="{{ route('admin.challenges.destroy', $challenge) }}" onsubmit="return confirm('Hapus tantangan ini?')">
                    @csrf
                    @method('DELETE')
                    <x-danger-button>Hapus tantangan</x-danger-button>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
