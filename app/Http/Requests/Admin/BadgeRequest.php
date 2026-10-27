<?php

namespace App\Http\Requests\Admin;

use App\Models\Badge;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BadgeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('badges', 'name')->ignore($this->route('badge'))],
            'description' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(Badge::TYPES))],
            'required_value' => ['required', 'numeric', 'min:1', 'max:1000000'],
            'icon' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
            'remove_icon' => ['nullable', 'boolean'],
            'is_active' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nama lencana',
            'description' => 'deskripsi',
            'type' => 'jenis syarat',
            'required_value' => 'nilai syarat',
            'icon' => 'gambar lencana',
            'is_active' => 'status aktif',
        ];
    }
}
