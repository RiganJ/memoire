<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentMethodRequest extends FormRequest
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
            'code' => [
                'required',
                Rule::in(['bca', 'dana', 'gopay']),
                Rule::unique('payment_methods', 'code')->ignore($this->route('paymentMethod')),
            ],
            'name' => ['required', 'string', 'max:80'],
            'account_name' => ['required', 'string', 'max:120'],
            'account_number' => ['required', 'string', 'max:80', 'regex:/\A[0-9+ .-]+\z/'],
            'instructions' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.in' => 'Pilih metode BCA, DANA, atau GoPay.',
            'account_number.regex' => 'Nomor rekening atau akun hanya boleh berisi angka dan tanda yang valid.',
        ];
    }
}
