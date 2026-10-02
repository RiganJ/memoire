<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CatalogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'category' => ['required', 'string', 'max:80'],
            'package' => ['required', Rule::in(['Essential', 'Signature', 'Bespoke'])],
            'color' => ['required', 'regex:/\A#[0-9A-Fa-f]{6}\z/'],
            'link' => ['nullable', 'url:http,https', 'max:2048'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'status' => ['required', Rule::in(['active', 'draft', 'archived'])],
        ];
    }

    public function messages(): array
    {
        return [
            'package.in' => 'Paket yang dipilih tidak valid.',
            'color.regex' => 'Warna harus menggunakan format hex, misalnya #6b3520.',
            'image.max' => 'Ukuran gambar desain maksimal 5 MB.',
            'image.mimes' => 'Gambar desain harus berformat JPG, PNG, atau WebP.',
            'status.in' => 'Status desain tidak valid.',
        ];
    }
}
