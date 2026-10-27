<x-app-layout>
    <x-slot name="title">Kelola Tantangan</x-slot>
    <x-slot name="header">
        <x-page-header title="Kelola Tantangan" subtitle="Buat, ubah, dan pantau tantangan pengurangan emisi.">
            <x-slot name="actions">
                <x-button-link :href="route('admin.challenges.create')">+ Buat Tantangan</x-button-link>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            <nav class="flex flex-wrap gap-2" aria-label="Saring periode">
                @foreach (['' => 'Semua'] + \App\Models\Challenge::PERIODS as $key => $label)
                    <a href="{{ route('admin.challenges.index', $key ? ['periode' => $key] : []) }}"
                       class="rounded-full px-3 py-1 text-sm font-medium {{ ($period ?? '') === $key ? 'bg-emerald-600 text-white' : 'bg-white text-gray-600 shadow-sm hover:bg-gray-50' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </nav>

            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="relative overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Tantangan</th>
                                <th class="px-6 py-3">Periode</th>
                                <th class="px-6 py-3 text-right">Target</th>
                                <th class="px-6 py-3 text-right">Hadiah</th>
                                <th class="px-6 py-3 text-right">Peserta</th>
                                <th class="px-6 py-3 text-right"><span class="sr-only">Aksi</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($challenges as $challenge)
                                <tr>
                                    <td class="px-6 py-4">
                                        <a href="{{ route('admin.challenges.show', $challenge) }}" class="font-medium text-gray-800 hover:underline">{{ $challenge->title }}</a>
                                        <p class="text-xs text-gray-500">{{ $challenge->category->icon }} {{ $challenge->category->name }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <x-period-pill :challenge="$challenge" />
                                        <p class="mt-1 text-xs text-gray-500">{{ $challenge->start_date->translatedFormat('d M') }} – {{ $challenge->end_date->translatedFormat('d M Y') }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-gray-800">{{ format_kg($challenge->reduction_target) }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-amber-700">⭐ {{ format_number($challenge->reward_points, 0) }}</td>
                                    <td class="px-6 py-4 text-right text-gray-800">{{ $challenge->active_participants_count }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right">
                                        <a href="{{ route('admin.challenges.edit', $challenge) }}" class="font-medium text-emerald-700 hover:underline">Ubah</a>
                                        <form method="POST" action="{{ route('admin.challenges.destroy', $challenge) }}" class="ms-3 inline"
                                              onsubmit="return confirm(@js('Hapus tantangan '.$challenge->title.'?'))">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="font-medium text-red-600 hover:underline disabled:cursor-not-allowed disabled:text-gray-400"
                                                    @disabled($challenge->active_participants_count > 0)
                                                    title="{{ $challenge->active_participants_count > 0 ? 'Sudah diikuti peserta' : 'Hapus tantangan' }}">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">Belum ada tantangan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($challenges->hasPages())
                    <div class="border-t border-gray-100 px-6 py-4">{{ $challenges->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
