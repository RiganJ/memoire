<?php

namespace App\Http\Requests\Admin;

use App\Models\InvitationGuest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvitationGuestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->string('name')->trim()->toString(),
            'phone' => $this->string('phone')->trim()->toString(),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:180'],
            'phone' => ['required', 'string', 'max:25', 'regex:/\A[0-9+() .-]+\z/'],
            'rsvp_status' => ['sometimes', Rule::in([
                InvitationGuest::RSVP_PENDING,
                InvitationGuest::RSVP_ATTENDING,
                InvitationGuest::RSVP_DECLINED,
            ])],
        ];
    }
}
