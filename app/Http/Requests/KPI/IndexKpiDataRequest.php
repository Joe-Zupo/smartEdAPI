<?php

namespace App\Http\Requests\KPI;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexKpiDataRequest extends FormRequest
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
            'page' => 'nullable|integer|min:1',
            'perPage' => 'nullable|integer|min:1|max:100',
            'sortBy' => 'nullable|string|in:|nullable',
            'sortOrder' => 'nullable|string|in:asc,desc',

            'kpi_id' => 'nullable|integer|exists:kpi_data,kpi_id',
            'academic_year_id' => 'nullable|integer|exists:kpi_data,academic_year_id',
            'school_type' => 'nullable|string|exists:school_types,name'
        ];
    }
}
