<?php

namespace App\Http\Requests\Admin;

use App\Models\Template;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $slug = $this->input('slug') ?: $this->input('name', '');

        $this->merge(['slug' => Str::slug((string) $slug)]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:180'],
            'slug' => ['required', 'string', 'max:100', 'regex:/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/', Rule::unique('templates', 'slug')->ignore($this->route('template')), Rule::notIn(Template::RESERVED_SLUGS)],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'template_zip' => ['nullable', 'file', 'extensions:zip', 'mimes:zip', 'max:20480'],
            'index_html' => ['nullable', 'file', 'extensions:html', 'max:5120'],
            'css_files' => ['nullable', 'array', 'max:100'],
            'css_files.*' => ['file', 'extensions:css', 'max:10240'],
            'js_files' => ['nullable', 'array', 'max:100'],
            'js_files.*' => ['file', 'extensions:js', 'max:10240'],
            'asset_files' => ['nullable', 'array', 'max:500'],
            'asset_files.*' => ['file', 'extensions:jpg,jpeg,png,webp,gif,svg,mp3,wav,ogg,mp4,webm,woff,woff2,ttf,json', 'max:25600'],
            'asset_paths' => ['nullable', 'array', 'max:500'],
            'asset_paths.*' => ['string', 'max:500'],
            'asset_file' => ['nullable', 'file', 'extensions:html,css,js,jpg,jpeg,png,webp,gif,svg,mp3,wav,ogg,mp4,webm,woff,woff2,ttf,json', 'max:25600'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.not_in' => 'Slug tersebut digunakan oleh route sistem.',
            'slug.unique' => 'Slug sudah digunakan template lain.',
            'template_zip.extensions' => 'Template harus berupa file ZIP.',
            'template_zip.mimes' => 'Isi file harus berupa arsip ZIP yang valid.',
            'asset_file.extensions' => 'Jenis file tersebut tidak diizinkan.',
            'index_html.extensions' => 'File halaman utama harus berformat HTML.',
            'css_files.*.extensions' => 'File pada bagian CSS harus berformat CSS.',
            'js_files.*.extensions' => 'File pada bagian JavaScript harus berformat JS.',
            'asset_files.*.extensions' => 'Folder aset berisi jenis file yang tidak diizinkan.',
        ];
    }
}
