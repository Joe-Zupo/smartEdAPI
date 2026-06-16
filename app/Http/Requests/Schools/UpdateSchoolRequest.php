<?php

namespace App\Http\Requests\Schools;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Helpers\updateValidator;

class UpdateSchoolRequest extends FormRequest
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
        $validUsers = User::role('School Account')->where('school_id', null)->pluck('name')->toArray();
        return [
            'school_name'               => ['sometimes', 'string', 'max:255', 'unique:schools,school_name'],
            'school_code'               => ['sometimes', 'string', 'max:50', Rule::unique('schools','school_code')->ignore($this->route('school'))],
            'year_established'          => ['sometimes', 'digits:4', 'integer'],
            'school_type'               => ['sometimes', 'exists:school_types,name'],
            'school_type_id'            => ['sometimes', 'exists:school_types,id'],
            //'address'                   => ['sometimes', 'string','max:255'],
                'street'                    => 'sometimes|string|max:255',
                'barangay'                  => 'sometimes|string|max:255|exists:barangays,name', // In the context that this is for mabalacat, currently mabalacat brngys are only avail
                'city'                      => 'sometimes|string|max:255',
                'province'                  => 'sometimes|string|max:255',
                'region'                    => 'sometimes|string|max:255',
                'district'                  => 'sometimes|string|max:255', //Await District Requirements (rn can be Compass directions)
            'latitude'                  => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude'                 => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],

            'school_head'               => 'string|exists:users,name|max:255|'. Rule::in($validUsers),

            //Principal I,Principal II,Principal III,Principal IV
            'position' =>   'required_with:school_head,school_head_id|string|
                            in:Principal I,Principal II,Principal III,Principal IV',
            //'image'             => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],

            // 'phone_number'                  => ['sometimes', 'string', 'max:15'],
            // 'head_email'                    => ['sometimes', 'string', 'email', 'max:255', 'unique:schools,head_email'],
    
        ];
    }
    
    public function messages(): array
        {
                $validUsers = User::role('School Account')->where('school_id', null)->pluck('name')->toArray();
                $string = implode(', ', $validUsers); 
                return [
                'position.in' =>
                    'Invalid position selected. Valid positions are: Principal IV,Head Teacher III,Teacher I,Principal III.',
                
                'school_head.in' => "Invalid Input! Valid users: ". $string
                    
            ];
        }

    public function prepareForValidation(): void
        {
            $school = $this->route('school');

            $data = $this->validateUpdate(
                $this->all(),
                $school,
                [
                    'school_name',
                    'school_code',
                    'year_established',
                    'school_type',
                    'school_type_id',
                    'region',
                    'district',
                    'latitude',
                    'longitude',
                    'school_head',
                    'position',
                    // 'phone_number',
                    // 'head_email',
                ]
            );

            $this->replace($data);
        }
}
