<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePublicOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'catalog_id' => ['required', 'integer', Rule::exists('catalogs', 'id')->where('status', 'active')],
            'package_id' => ['required', 'integer', Rule::exists('service_packages', 'id')->where('is_active', true)],
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^62[0-9]{8,15}$/'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'answers' => ['required', 'array'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $phone = preg_replace('/\D+/', '', (string) $this->input('phone')) ?? '';

        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }

        $this->merge(['phone' => $phone]);
    }
}
