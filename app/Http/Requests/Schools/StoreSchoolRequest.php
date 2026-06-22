<?php

namespace App\Http\Requests\Schools;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

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
        $validUsers = User::role('School Account')->where('school_id', null)->pluck('name')->toArray();
        return [
            'school_name'               => 'required|string|max:255|unique:schools,school_name',
            'school_code'               => 'required|string|max:50|unique:schools,school_code',
            'year_established'          => 'required|digits:4|integer',

            'school_type'               => 'required_without:school_type_id|exists:school_types,name|nullable',
            'school_type_id'            => 'required_without:school_type|exists:school_types,id|nullable',

            'school_head'               => ['required','string','max:255', Rule::in($validUsers)],

            //Principal I,Principal II,Principal III,Principal IV
            'position'                  => 'required_with:school_head,school_head_id|string|
                                            in:Principal I,Principal II,Principal III,Principal IV',

            //'phone_number'             => ['required', 'string', 'max:15'],
            //'head_email'               => ['required', 'string', 'email', 'max:255', 'unique:schools,head_email'],

            //'address'                   => 'required|string|max:255',

                'street'                    => 'required|string|max:255',
                'barangay'                  => 'required|string|max:255|exists:barangays,name', // In the context that this is for mabalacat, currently mabalacat brngys are only avail
                'city'                      => 'required|string|max:255',
                'province'                  => 'required|string|max:255',
                'region'                    => 'required|string|max:255',
                'district'                  => 'required|string|max:255|in:North,South,East,West', //Await District Requirements (rn can be Compass directions)

            'latitude'                  => ['required', 'nullable', 'numeric', 'between:-90,90'],
            'longitude'                 => ['required', 'nullable', 'numeric', 'between:-180,180'],
            'image'                     => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }
    public function messages(): array
        {
                return [
                'position.required_if' =>
                    'School Accounts must have a position. Valid positions are: Principal IV,Head Teacher III,Teacher I,Principal III.',

                'position.in' =>
                    'Invalid position selected. Valid positions are: Principal IV,Head Teacher III,Teacher I,Principal III.',
            ];
        }
}
