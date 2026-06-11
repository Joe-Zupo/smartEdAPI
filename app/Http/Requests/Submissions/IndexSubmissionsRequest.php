<?php

namespace App\Http\Requests\Submissions;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\AcademicYear;
use App\Models\School;

class IndexSubmissionsRequest extends FormRequest
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

            //'sortBy' => 'nullable|string|in:id,school_name,school_code,created_at',
            //'sortOrder' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',

            'type' => 'in:enrollment,resource,information',
            'status' => 'in:pending,approved,returned',
            'search' => 'string',
        ];
    }
}
