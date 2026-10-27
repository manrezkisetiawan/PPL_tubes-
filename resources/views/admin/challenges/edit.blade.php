<x-app-layout>
    <x-slot name="title">Ubah Tantangan</x-slot>
    <x-slot name="header">
        <x-page-header title="Ubah Tantangan" :subtitle="$challenge->title" />
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('admin.challenges.update', $challenge) }}" class="rounded-lg bg-white p-6 shadow-sm">
                @method('PUT')
                @include('admin.challenges._form')
            </form>
        </div>
    </div>
</x-app-layout>
