<?php

namespace App\Http\Requests\DivisionLeadership;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDivisionLeadershipRequest extends FormRequest
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
            'name' => 'sometimes|string|',
            'position' => 'sometimes|string|in:Schools Division Superintendent,Assistant Schools Division Superintendent',
            'is_oic' => 'sometimes|boolean',
            'current_term' => 'sometimes|boolean',
            'term_start' => 'sometimes|integer|digits:4|max:' .date('Y'),
            'term_end' => 'sometimes|integer|gte:term_start|digits:4',
        ];
    }
}
