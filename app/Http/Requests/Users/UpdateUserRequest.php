<?php

namespace App\Http\Requests\Users;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\updateValidator;

class UpdateUserRequest extends FormRequest
{
    use updateValidator;
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
            'name' => ['sometimes', 'string', 'max:255'],
            // 'position' => ['in:Principal IV,Head Teacher III,Teacher I,Principal III'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email'],
            'username' => ['sometimes', 'string', 'max:255', 'unique:users,username'],
            'phone_number' => ['sometimes', 'string', 'max:15'],
        ];
    }
        // public function messages(): array
        // {
        //         return [
        //         'position.required_if' =>
        //             'School Accounts must have a position. Valid positions are: Principal IV,Head Teacher III,Teacher I,Principal III.',

        //         'position.in' =>
        //             'Invalid position selected. Valid positions are: Principal IV,Head Teacher III,Teacher I,Principal III.',
        //     ];
        // }
        public function prepareForValidation(): void
        {
            $user = $this->route('user');

            $data = $this->validateUpdate(
                $this->all(),
                $user,
                [
                    'name',
                    'email',
                    'username',
                    'phone_number',
                ]
            );

            $this->replace($data);
        }
}
