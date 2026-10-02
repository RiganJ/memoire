<?php

namespace App\Http\Requests\Admin;

use App\Models\Invitation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $slug = $this->input('slug') ?: $this->input('name', '');
        $guests = collect($this->input('guests', []))
            ->map(function (mixed $guest): array {
                $guest = is_array($guest) ? $guest : [];

                return [
                    'name' => trim((string) ($guest['name'] ?? '')),
                    'phone' => trim((string) ($guest['phone'] ?? '')),
                ];
            })
            ->filter(fn (array $guest): bool => $guest['name'] !== '' || $guest['phone'] !== '')
            ->values()
            ->all();

        $this->merge([
            'slug' => Str::slug((string) $slug),
            'guests' => $guests,
        ]);
    }

    public function rules(): array
    {
        return [
            'template_id' => ['required', 'integer', Rule::exists('templates', 'id')->where('status', 'published')],
            'name' => ['required', 'string', 'max:180'],
            'slug' => ['required', 'string', 'max:100', 'regex:/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/', Rule::unique('invitations', 'slug')->ignore($this->route('invitation')), Rule::notIn(Invitation::RESERVED_SLUGS)],
            'groom_name' => ['required', 'string', 'max:120'],
            'bride_name' => ['required', 'string', 'max:120'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'guests' => ['array', 'max:100'],
            'guests.*.name' => ['required', 'string', 'max:180'],
            'guests.*.phone' => ['required', 'string', 'max:25', 'regex:/\A[0-9+() .-]+\z/'],
        ];
    }
}
