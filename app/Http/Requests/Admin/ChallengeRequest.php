<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChallengeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    public function rules(): array
    {
        $endDate = ['required', 'date', 'after_or_equal:start_date'];
        if ($this->isMethod('post')) {
            // Tantangan baru tidak boleh dibuat untuk periode yang sudah lewat.
            $endDate[] = 'after_or_equal:today';
        }

        return [
            'title' => ['required', 'string', 'max:150', Rule::unique('challenges', 'title')->ignore($this->route('challenge'))],
            'category_id' => ['required', Rule::exists('categories', 'id')->where('is_active', true)],
            'description' => ['nullable', 'string', 'max:2000'],
            'start_date' => ['required', 'date'],
            'end_date' => $endDate,
            'reduction_target' => ['required', 'numeric', 'min:0.1', 'max:100000'],
            'reward_points' => ['required', 'integer', 'min:0', 'max:100000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'judul tantangan',
            'category_id' => 'kategori',
            'description' => 'deskripsi',
            'start_date' => 'tanggal mulai',
            'end_date' => 'tanggal selesai',
            'reduction_target' => 'target penurunan',
            'reward_points' => 'hadiah poin',
        ];
    }

    public function messages(): array
    {
        return [
            'end_date.after_or_equal' => 'Tanggal selesai harus sama dengan atau setelah tanggal mulai dan tidak boleh sudah lewat.',
        ];
    }
}
