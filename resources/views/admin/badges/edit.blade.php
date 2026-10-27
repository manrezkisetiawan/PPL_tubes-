<x-app-layout>
    <x-slot name="title">Ubah Lencana</x-slot>
    <x-slot name="header">
        <x-page-header title="Ubah Lencana" subtitle="{{ $badge->name }} · sudah diraih {{ $badge->users_count }} pengguna" />
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl space-y-4 px-4 sm:px-6 lg:px-8">
            @if ($badge->users_count > 0)
                <div class="rounded-lg border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
                    Perubahan syarat hanya berlaku untuk pengguna yang belum meraih lencana ini. Lencana yang sudah diraih tetap dimiliki.
                </div>
            @endif

            <form method="POST" action="{{ route('admin.badges.update', $badge) }}" enctype="multipart/form-data" class="rounded-lg bg-white p-6 shadow-sm">
                @method('PUT')
                @include('admin.badges._form')
            </form>
        </div>
    </div>
</x-app-layout>
