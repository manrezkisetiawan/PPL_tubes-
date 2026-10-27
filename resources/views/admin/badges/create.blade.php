<x-app-layout>
    <x-slot name="title">Tambah Lencana</x-slot>
    <x-slot name="header">
        <x-page-header title="Tambah Lencana" subtitle="Tentukan nama, jenis syarat, dan nilai syarat perolehannya." />
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('admin.badges.store') }}" enctype="multipart/form-data" class="rounded-lg bg-white p-6 shadow-sm">
                @include('admin.badges._form')
            </form>
        </div>
    </div>
</x-app-layout>
