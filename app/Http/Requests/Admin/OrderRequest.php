<?php

namespace App\Http\Requests\Admin;

use App\Models\ServicePackage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'order_number' => [
                'nullable',
                'string',
                'max:30',
                'regex:/^[A-Za-z0-9\-]+$/',
                Rule::unique('orders', 'order_number')->ignore($this->route('order')),
            ],
            'customer_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:25', 'regex:/^[0-9+()\-\s]+$/'],
            'package' => ['required', Rule::in([
                ...ServicePackage::query()->pluck('name')->all(),
                'Essential',
                'Signature',
                'Bespoke',
            ])],
            'event_type' => ['required', 'string', 'max:80'],
            'total' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'event_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['waiting', 'process', 'revision', 'ready', 'done', 'cancelled'])],
            'payment_status' => ['required', Rule::in(['unpaid', 'paid', 'refunded'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'order_number.regex' => 'Nomor pesanan hanya boleh berisi huruf, angka, dan tanda hubung.',
            'phone.regex' => 'Format nomor telepon tidak valid.',
            'status.in' => 'Status pesanan tidak valid.',
            'payment_status.in' => 'Status pembayaran tidak valid.',
        ];
    }
}
