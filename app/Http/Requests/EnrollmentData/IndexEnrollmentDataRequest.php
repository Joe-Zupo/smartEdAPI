<?php

namespace App\Http\Requests\EnrollmentData;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\AcademicYear;
use App\Models\School;
use Illuminate\Validation\Validator;

class IndexEnrollmentDataRequest extends FormRequest
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
            'all' => 'nullable|in:true,false',

            'sortBy' => 'nullable|string|in:id,school_name,school_code,created_at',
            'sortOrder' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',

            'academic_year' => ['exists:academic_years,academic_year', Rule::in(AcademicYear::whereIn('status', ['active', 'default'])->pluck('academic_year')->toArray())],
            'school_name' => ['nullable', 'exists:schools,school_name', Rule::in(School::pluck('school_name')->toArray())],
        ];
    }
}
