<x-app-layout>
    <x-slot name="title">Tantangan</x-slot>
    <x-slot name="header">
        <x-page-header title="Tantangan" subtitle="Ikuti tantangan pengurangan emisi dan kumpulkan EcoPoint.">
            <x-slot name="actions">
                <x-button-link :href="route('participations.index')" variant="secondary">Tantangan Saya</x-button-link>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-8 px-4 sm:px-6 lg:px-8">
            <section>
                <h3 class="text-lg font-semibold text-gray-800">Sedang berjalan</h3>
                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @forelse ($running as $challenge)
                        @include('challenges.partials.card')
                    @empty
                        <p class="text-sm text-gray-500">Tidak ada tantangan yang sedang berjalan.</p>
                    @endforelse
                </div>
            </section>

            <section>
                <h3 class="text-lg font-semibold text-gray-800">Akan datang</h3>
                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @forelse ($upcoming as $challenge)
                        @include('challenges.partials.card')
                    @empty
                        <p class="text-sm text-gray-500">Belum ada tantangan yang dijadwalkan.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
