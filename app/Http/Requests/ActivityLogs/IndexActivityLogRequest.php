<?php

namespace App\Http\Requests\ActivityLogs;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\School;

class IndexActivityLogRequest extends FormRequest
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
            'filter.action' => ['in:Logged In,Logged Out,Submitted Data,Returned Data,Approved Data'],
            'filter.school' => ['exists:schools,school_name', Rule::in(School::pluck('school_name')->toArray())],
            'search' => 'string',
            'per_page' => 'integer',
            'page' => 'integer'
        ];
    }
}
