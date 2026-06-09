<?php

namespace App\Http\Requests\Users;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'school' => [
                'exists:schools,school_name',
            ],
            //'position' => ['required_if:role,School Account', 'in:Principal IV,Head Teacher III,Teacher I'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone_number' => ['required', 'string', 'max:15'],
        ];
    }
    // public function messages(): array
    // {
    //     return [
    //         'position.required_if' =>
    //             'School Accounts must have a position. Valid positions are: Principal IV, Principal III, Officer In Charge, Head Teacher III.',

    //         'position.in' =>
    //             'Invalid position selected. Valid positions are: Principal IV, Principal III, Officer In Charge, Head Teacher III.',
    //     ];
    // }

}
