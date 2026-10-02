<?php

namespace App\Http\Requests\Customer;

use App\Models\InvitationGuest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvitationGuestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->attributes->has('customerInvitation');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:180'],
            'phone' => ['required', 'string', 'max:25', 'regex:/\A[0-9+() .-]+\z/'],
            'rsvp_status' => ['required', Rule::in([
                InvitationGuest::RSVP_PENDING,
                InvitationGuest::RSVP_ATTENDING,
                InvitationGuest::RSVP_DECLINED,
            ])],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->string('name')->trim()->toString(),
            'phone' => $this->string('phone')->trim()->toString(),
        ]);
    }
}
