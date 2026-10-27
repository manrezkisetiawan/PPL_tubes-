@csrf

<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div class="md:col-span-2">
        <x-input-label for="name" value="Nama lencana" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $badge->name)" required maxlength="100" />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="description" value="Deskripsi" />
        <x-text-input id="description" name="description" type="text" class="mt-1 block w-full" :value="old('description', $badge->description)" maxlength="255" />
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="type" value="Jenis syarat" />
        <select id="type" name="type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            @foreach (\App\Models\Badge::TYPES as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $badge->type) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('type')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="required_value" value="Nilai syarat" />
        <x-text-input id="required_value" name="required_value" type="number" min="1" step="1" class="mt-1 block w-full"
                      :value="old('required_value', $badge->required_value !== null ? (float) $badge->required_value : null)" required />
        <p class="mt-1 text-xs text-gray-500">Contoh: 7 untuk "7 hari berturut-turut", 500 untuk "500 EcoPoint".</p>
        <x-input-error :messages="$errors->get('required_value')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="icon" value="Gambar lencana (opsional, JPG/PNG/WEBP maks. 1 MB)" />
        <div class="mt-2 flex items-center gap-4">
            @if ($badge->exists)
                <x-badge-icon :badge="$badge" />
            @endif
            <input id="icon" name="icon" type="file" accept="image/png,image/jpeg,image/webp"
                   class="block w-full text-sm text-gray-600 file:me-4 file:rounded-md file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100">
        </div>
        @if ($badge->icon)
            <label class="mt-2 inline-flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remove_icon" value="1" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                Hapus gambar dan gunakan ikon bawaan
            </label>
        @endif
        <x-input-error :messages="$errors->get('icon')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $badge->is_active))
                   class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
            Aktif (lencana dapat diraih pengguna)
        </label>
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <x-primary-button>Simpan</x-primary-button>
    <a href="{{ route('admin.badges.index') }}" class="text-sm text-gray-600 hover:underline">Batal</a>
</div>
