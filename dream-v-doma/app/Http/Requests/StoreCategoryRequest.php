<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'exists:categories,id'],
            'status' => ['nullable', 'boolean'],
            'translations' => ['required', 'array'],

            'translations.*.locale' => ['required', 'string'],
            'translations.*.name' => ['required', 'string'],
            'translations.*.meta_title' => ['required', 'string'],
            'translations.*.meta_description' => ['required', 'string'],
        ];
    }
}
