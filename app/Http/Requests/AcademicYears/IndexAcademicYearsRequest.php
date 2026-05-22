<?php

namespace App\Http\Requests\AcademicYears;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexAcademicYearsRequest extends FormRequest
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
            'status' => 'string|in:active,default,upcoming,archived',
            'academic_year' => 'string|exists:academic_years,academic_year|nullable',
            'per_page' => 'integer',
            'page' => 'integer'
        ];
    }
}
