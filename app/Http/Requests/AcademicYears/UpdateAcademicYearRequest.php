<?php

namespace App\Http\Requests\AcademicYears;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAcademicYearRequest extends FormRequest
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
            'start_date' => 'sometimes|date|before:end_date|unique:academic_years,start_date',
            'end_date' => 'sometimes|date|after:start_date|unique:academic_years,end_date',
            'academic_year' => 'sometimes|string|unique:academic_years,academic_year',
        ];
    }
}
