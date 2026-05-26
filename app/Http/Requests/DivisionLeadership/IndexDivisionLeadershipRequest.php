<?php

namespace App\Http\Requests\DivisionLeadership;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexDivisionLeadershipRequest extends FormRequest
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
            'search' => 'string|nullable',
            'oic' => 'boolean|default:false',
            //'all' => 'boolean|default:false',
            'position' => 'string|in:Schools Division Superintendent,Assistant Schools Division Superintendent|nullable',
            'per_page' => 'integer',
            'page' => 'integer'
        ];
    }
}
