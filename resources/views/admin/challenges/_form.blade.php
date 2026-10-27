@csrf

<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div class="md:col-span-2">
        <x-input-label for="title" value="Judul tantangan" />
        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $challenge->title)" required maxlength="150" />
        <x-input-error :messages="$errors->get('title')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="category_id" value="Kategori emisi" />
        <select id="category_id" name="category_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            <option value="">Pilih kategori…</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $challenge->category_id) === (string) $category->id)>
                    {{ $category->icon }} {{ $category->name }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="reward_points" value="Hadiah EcoPoint" />
        <x-text-input id="reward_points" name="reward_points" type="number" min="0" step="1" class="mt-1 block w-full" :value="old('reward_points', $challenge->reward_points)" required />
        <x-input-error :messages="$errors->get('reward_points')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="start_date" value="Tanggal mulai" />
        <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" :value="old('start_date', $challenge->start_date?->toDateString())" required />
        <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="end_date" value="Tanggal selesai" />
        <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full" :value="old('end_date', $challenge->end_date?->toDateString())" required />
        <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="reduction_target" value="Target penurunan (kg CO₂e yang harus dihemat)" />
        <x-text-input id="reduction_target" name="reduction_target" type="number" min="0.1" step="0.1" class="mt-1 block w-full"
                      :value="old('reduction_target', $challenge->reduction_target !== null ? (float) $challenge->reduction_target : null)" required />
        <p class="mt-1 text-xs text-gray-500">Penghematan dihitung dari selisih garis dasar harian peserta dengan emisi kategori ini selama tantangan.</p>
        <x-input-error :messages="$errors->get('reduction_target')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="description" value="Deskripsi" />
        <textarea id="description" name="description" rows="4" maxlength="2000"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('description', $challenge->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <x-primary-button>Simpan</x-primary-button>
    <a href="{{ $challenge->exists ? route('admin.challenges.show', $challenge) : route('admin.challenges.index') }}" class="text-sm text-gray-600 hover:underline">Batal</a>
</div>
