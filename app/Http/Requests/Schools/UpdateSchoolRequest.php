<?php

namespace App\Http\Requests\Schools;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSchoolRequest extends FormRequest
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
            'school_name'              => ['sometimes', 'string', 'max:255'],
            'school_code'              => ['sometimes', 'string', 'max:50', Rule::unique('schools','school_ code')->ignore($this->route('school'))],
            'year_established'  => ['sometimes', 'digits:4', 'integer'],
            'school_type_id'    => ['sometimes', 'exists:school_types,id'],
            'street'            => ['sometimes', 'string', 'max:255'],
            'city'              => ['sometimes', 'string', 'max:255'],
            'barangay_id'       => ['sometimes', 'exists:barangays,id'],
            'province'          => ['sometimes', 'string', 'max:255'],
            'district'          => ['sometimes', 'string', 'max:255'],
            'region'            => ['sometimes', 'string', 'max:255'],
            //'image'             => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }
}
