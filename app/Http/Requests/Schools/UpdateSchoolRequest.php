<?php

namespace App\Http\Requests\Schools;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User;

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
            'school_name'               => ['sometimes', 'string', 'max:255'],
            'school_code'               => ['sometimes', 'string', 'max:50', Rule::unique('schools','school_ code')->ignore($this->route('school'))],
            'year_established'          => ['sometimes', 'digits:4', 'integer'],
            'school_type'               => ['sometimes', 'exists:school_types,name'],
            'school_type_id'            => ['sometimes', 'exists:school_types,id'],
            'address'                   => ['sometimes', 'string','max:255'],
            'district'                  => ['sometimes', 'string', 'max:255'],
            'latitude'                  => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude'                 => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],

            'school_head'               => 'string|exists:users,name',
            'school_head_id'            => ['integer|exists:users,id', function ($attribute, $value, $fail) {
                    $user = User::find($value);

                    if (!$user || !$user->hasRole('School Account')) {
                        $fail('The selected user is not a School Account.');
                    }

                    if (isset($user->school_id)){
                        $fail('User already has a school assigned to them!');
                    }
                }],
            //'image'             => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }
}
