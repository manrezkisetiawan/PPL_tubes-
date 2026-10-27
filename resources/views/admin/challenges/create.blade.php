<x-app-layout>
    <x-slot name="title">Buat Tantangan</x-slot>
    <x-slot name="header">
        <x-page-header title="Buat Tantangan" subtitle="Tantangan berjangka untuk mendorong satu perubahan perilaku." />
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('admin.challenges.store') }}" class="rounded-lg bg-white p-6 shadow-sm">
                @include('admin.challenges._form')
            </form>
        </div>
    </div>
</x-app-layout>
