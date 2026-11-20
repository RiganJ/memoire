<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvitationGuestsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->attributes->has('customerInvitation');
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'guests' => ['required', 'array', 'min:1', 'max:100'],
            'guests.*.name' => ['required', 'string', 'max:180', 'distinct:ignore_case'],
            'guests.*.phone' => ['nullable', 'string', 'max:25', 'regex:/\A[0-9+() .-]+\z/'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $guests = collect($this->input('guests', []))
            ->filter(fn (mixed $guest): bool => is_array($guest))
            ->map(fn (array $guest): array => [
                'name' => trim((string) ($guest['name'] ?? '')),
                'phone' => trim((string) ($guest['phone'] ?? '')) ?: null,
            ])
            ->values()
            ->all();

        $this->merge(['guests' => $guests]);
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'guests.required' => 'Tambahkan minimal satu nama tamu ke daftar.',
            'guests.*.name.required' => 'Nama tamu wajib diisi.',
            'guests.*.name.distinct' => 'Nama tamu tidak boleh sama dalam satu daftar.',
            'guests.*.phone.regex' => 'Nomor WhatsApp hanya boleh berisi angka dan tanda telepon.',
        ];
    }
}
