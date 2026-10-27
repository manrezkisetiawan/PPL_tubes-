<x-app-layout>
    <x-slot name="title">Aktivitas</x-slot>
    <x-slot name="header">
        <x-page-header title="Catat Aktivitas" subtitle="Setiap aktivitas dihitung menjadi kg CO₂e dan dipakai untuk progres tantangan serta lencana." />
    </x-slot>

    <div class="py-8">
        <div class="mx-auto grid max-w-7xl grid-cols-1 gap-6 px-4 sm:px-6 lg:grid-cols-3 lg:px-8">
            <section class="rounded-lg bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-800">Aktivitas baru</h3>

                <form method="POST" action="{{ route('activities.store') }}" class="mt-4 space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="emission_factor_id" value="Jenis aktivitas" />
                        <select id="emission_factor_id" name="emission_factor_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Pilih aktivitas…</option>
                            @foreach ($categories as $category)
                                <optgroup label="{{ $category->icon }} {{ $category->name }}">
                                    @foreach ($category->emissionFactors as $factor)
                                        <option value="{{ $factor->id }}" @selected(old('emission_factor_id') == $factor->id)>
                                            {{ $factor->name }} ({{ format_number($factor->factor_value, 4) }} kg/{{ $factor->unit }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('emission_factor_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="amount" value="Jumlah (km, kWh, porsi, atau kg sesuai satuan)" />
                        <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1 block w-full" :value="old('amount')" required />
                        <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="activity_date" value="Tanggal" />
                        <x-text-input id="activity_date" name="activity_date" type="date" class="mt-1 block w-full" :value="old('activity_date', today()->toDateString())" :max="today()->toDateString()" required />
                        <x-input-error :messages="$errors->get('activity_date')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="note" value="Catatan (opsional)" />
                        <x-text-input id="note" name="note" type="text" class="mt-1 block w-full" :value="old('note')" maxlength="255" />
                        <x-input-error :messages="$errors->get('note')" class="mt-2" />
                    </div>

                    <x-primary-button>Simpan</x-primary-button>
                </form>
            </section>

            <section class="min-w-0 overflow-hidden rounded-lg bg-white shadow-sm lg:col-span-2">
                <h3 class="px-6 pt-6 text-lg font-semibold text-gray-800">Riwayat aktivitas</h3>

                <div class="relative mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-medium uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Tanggal</th>
                                <th class="px-6 py-3">Aktivitas</th>
                                <th class="px-6 py-3 text-right">Jumlah</th>
                                <th class="px-6 py-3 text-right">Emisi</th>
                                <th class="px-6 py-3"><span class="sr-only">Aksi</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($activities as $activity)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-3 text-gray-600">{{ $activity->activity_date->translatedFormat('d M Y') }}</td>
                                    <td class="px-6 py-3">
                                        <p class="text-gray-800">{{ $activity->emissionFactor->category->icon }} {{ $activity->emissionFactor->name }}</p>
                                        @if ($activity->note)
                                            <p class="text-xs text-gray-500">{{ $activity->note }}</p>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-3 text-right text-gray-600">{{ format_number($activity->amount) }} {{ $activity->emissionFactor->unit }}</td>
                                    <td class="whitespace-nowrap px-6 py-3 text-right font-medium text-gray-800">{{ format_kg($activity->emission) }}</td>
                                    <td class="px-6 py-3 text-right">
                                        <form method="POST" action="{{ route('activities.destroy', $activity) }}" onsubmit="return confirm('Hapus catatan aktivitas ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm font-medium text-red-600 hover:underline">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">Belum ada aktivitas tercatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($activities->hasPages())
                    <div class="border-t border-gray-100 px-6 py-4">{{ $activities->links() }}</div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
