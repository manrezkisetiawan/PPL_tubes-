<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'emission_factor_id' => ['required', Rule::exists('emission_factors', 'id')->where('is_active', true)],
            'amount' => ['required', 'numeric', 'gt:0', 'max:100000'],
            'activity_date' => ['required', 'date', 'before_or_equal:today'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'emission_factor_id' => 'jenis aktivitas',
            'amount' => 'jumlah',
            'activity_date' => 'tanggal',
            'note' => 'catatan',
        ];
    }

    public function messages(): array
    {
        return [
            'emission_factor_id.exists' => 'Faktor emisi untuk aktivitas ini sedang dinonaktifkan. Silakan pilih aktivitas lain.',
            'amount.gt' => 'Jumlah harus lebih besar dari nol.',
            'activity_date.before_or_equal' => 'Tanggal aktivitas tidak boleh melebihi hari ini.',
        ];
    }
}
