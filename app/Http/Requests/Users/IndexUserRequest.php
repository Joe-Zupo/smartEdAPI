<?php

namespace App\Http\Requests\Users;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexUserRequest extends FormRequest
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
            'role' => 'in:School Account,Division Admin,System Admin',
            'is_active' => 'in:true,false',
            'search' => 'string',
            'has_school' => 'in:true,false',
            'per_page' => 'integer',
            'page' => 'integer'
        ];
    }
}
