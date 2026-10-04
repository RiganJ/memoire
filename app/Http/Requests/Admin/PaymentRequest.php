<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
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
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'transaction_number' => [
                'nullable',
                'string',
                'max:30',
                'regex:/\A[A-Za-z0-9-]+\z/',
                Rule::unique('payments', 'transaction_number')->ignore($this->route('payment')),
            ],
            'amount' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'status' => ['required', Rule::in(['pending', 'success', 'paid', 'failed', 'expired', 'cancelled', 'refunded'])],
            'paid_at' => ['required_if:status,success,paid,refunded', 'nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'transaction_number.regex' => 'Nomor transaksi hanya boleh berisi huruf, angka, dan tanda hubung.',
            'status.in' => 'Status pembayaran tidak valid.',
        ];
    }
}
