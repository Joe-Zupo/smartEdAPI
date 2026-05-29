<?php

namespace App\Http\Requests\Schools;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexSchoolRequest extends FormRequest
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
            'school_name' => 'nullable|string|max:255',
            'school_code' => 'nullable|string|max:255',
            'school_type' => 'nullable|string|exists:school_types,name',
            'district' => 'nullable|string|in:North,South,East,West',

            'sortBy' => 'nullable|string|in:id,school_name,school_code,created_at',
            'sortOrder' => 'nullable|string|in:asc,desc',

            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ];
    }
}
