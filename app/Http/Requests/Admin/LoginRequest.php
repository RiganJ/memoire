<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc', 'max:254'],
            'password' => ['required', 'string', 'max:128', Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'captcha' => ['required', 'string', 'size:5', 'alpha_num:ascii'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Masukkan alamat email yang valid.',
            'password.required' => 'Kata sandi wajib diisi.',
            'captcha.required' => 'Kode verifikasi wajib diisi.',
            'captcha.size' => 'Kode verifikasi harus terdiri dari 5 karakter.',
            'captcha.alpha_num' => 'Kode verifikasi hanya boleh berisi huruf dan angka.',
        ];
    }
}
