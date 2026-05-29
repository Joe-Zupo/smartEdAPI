<?php

namespace App\Http\Requests\DivisionLeadership;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDivisionLeadershipRequest extends FormRequest
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
            'name' => 'required|string|',
            'position' => 'required|string|in:Schools Division Superintendent,Assistant Schools Division Superintendent',
            'is_oic' => 'required|boolean',
            'current_term' => 'required|boolean',
            'term_start' => 'required|integer|digits:4|max:' .date('Y'),
            'term_end' => 'required_if:current_term,false|integer|gte:term_start|digits:4|nullable',
        ];
    }
}
