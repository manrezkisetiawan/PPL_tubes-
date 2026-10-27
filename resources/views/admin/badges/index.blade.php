<x-app-layout>
    <x-slot name="title">Kelola Lencana</x-slot>
    <x-slot name="header">
        <x-page-header title="Kelola Lencana" subtitle="Daftar lencana beserta syarat perolehannya.">
            <x-slot name="actions">
                <x-button-link :href="route('admin.badges.create')">+ Tambah Lencana</x-button-link>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="relative overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Lencana</th>
                                <th class="px-6 py-3">Syarat</th>
                                <th class="px-6 py-3 text-right">Peraih</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right"><span class="sr-only">Aksi</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($badges as $badge)
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <x-badge-icon :badge="$badge" size="sm" :locked="! $badge->is_active" />
                                            <div>
                                                <p class="font-medium text-gray-800">{{ $badge->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $badge->description }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="text-gray-800">{{ $badge->requirement_text }}</p>
                                        <p class="text-xs text-gray-500">{{ $badge->type_label }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-right text-gray-800">{{ $badge->users_count }}</td>
                                    <td class="px-6 py-4">
                                        @if ($badge->is_active)
                                            <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">Aktif</span>
                                        @else
                                            <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right">
                                        <a href="{{ route('admin.badges.edit', $badge) }}" class="font-medium text-emerald-700 hover:underline">Ubah</a>
                                        <form method="POST" action="{{ route('admin.badges.destroy', $badge) }}" class="ms-3 inline"
                                              onsubmit="return confirm(@js('Hapus lencana '.$badge->name.'?'))">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="font-medium text-red-600 hover:underline disabled:cursor-not-allowed disabled:text-gray-400"
                                                    @disabled($badge->users_count > 0)
                                                    title="{{ $badge->users_count > 0 ? 'Sudah diraih pengguna; nonaktifkan saja' : 'Hapus lencana' }}">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">Belum ada lencana.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($badges->hasPages())
                    <div class="border-t border-gray-100 px-6 py-4">{{ $badges->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
