<?php

namespace App\Http\Requests\Schools;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;

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

            //interchangable inputs:
            'school_type'               => 'required_without:school_type_id|exists:school_types,name|nullable',
            'school_type_id'            => 'required_without:school_type|exists:school_types,id|nullable',

            'school_head'               => 'required_without:school_head_id|string|exists:users,name',
            'school_head_id'            => ['required_without:school_head', 'integer|exists:users,id', function ($attribute, $value, $fail) {
                    $user = User::find($value);

                    if (!$user || !$user->hasRole('School Account')) {
                        $fail('The selected user is not a School Account.');
                    }

                    if (isset($user->school_id)){
                        $fail('User already has a school assigned to them!');
                    }
                }],

            'phone_number'             => ['required', 'string', 'max:15'],
            'head_email'                    => ['required', 'string', 'email', 'max:255', 'unique:schools,head_email'],
            
            
            'position' => 'required_with:school_head,school_head_id|string|in:Principal IV,Head Teacher III,Teacher I',

            'address'                   => 'required|string|max:255',
            'district'                  => 'required|string|max:255',
            //'image'                     => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }
}
