<?php

namespace App\Http\Requests\Schools;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSchoolRequest extends FormRequest
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
            'school_name'               => 'required|string|max:255',
            'school_code'               => 'required|string|max:50|unique:schools,school_code',
            'year_established'          => 'required|digits:4|integer',
            'school_type'               => 'required_without:school_type_id|exists:school_types,name|nullable',
            'school_type_id'            => 'required_without:school_type|exists:school_types,id|nullable',
            'address'                   => 'required|string|max:255',
            'district'                  => 'required|string|max:255',
            //'image'                     => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }
}
