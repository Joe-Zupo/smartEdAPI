<?php

namespace App\Http\Requests\Submissions;

use App\Helpers\updateValidator;
use App\Models\School;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubmissionsRequest extends FormRequest
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
        $submission = $this->route('submission');

        return [
            'details' => ['required', 'array', 'min:1'],

            'details.*.grade_level' => [
                'exclude_unless:type,enrollment',
                'required',
                'string',
                'exists:grade_levels,name',
            ],

            'details.*.male_count' => ['exclude_unless:type,enrollment', 'integer', 'min:0'],
            'details.*.female_count' => ['exclude_unless:type,enrollment', 'integer', 'min:0'],

            'details.*.resource_name' => [
                'exclude_unless:type,resource',
                'required',
                'in:Classrooms,Teachers,Seats,Learning Materials',
                'distinct'
            ],
            'details.*.inventory' => ['exclude_unless:type,resource', 'required', 'integer', 'min:0'],
            'details.*.requirement' => ['exclude_unless:type,resource', 'required', 'integer', 'min:0'],
            'details.*.school_name' => [
                'exclude_unless:type,information',
                'unique:school_information_drafts,school_name',
                'sometimes',
                'string',
                'max:255',
            ],

            'details.*.school_code' => [
                'exclude_unless:type,information',
                'sometimes',
                'string',
                'max:50',
                Rule::unique('schools', 'school_code')->ignore(
                    optional($this->route('submission'))->school_id
                ),
            ],


            'details.*.year_established' => [
                'exclude_unless:type,information',
                'sometimes',
                'digits:4',
                'integer',
            ],

            'details.*.school_type' => [
                'exclude_unless:type,information',
                'sometimes',
                'exists:school_types,name',
            ],


            'details.*.street' => 'exclude_unless:type,information|string|max:255',
            'details.*.barangay' => 'exclude_unless:type,information|string|max:255|exists:barangays,name', // In the context that this is for mabalacat, currently mabalacat brngys are only avail
            'details.*.city' => 'exclude_unless:type,information|string|max:255',
            'details.*.province' => 'exclude_unless:type,information|string|max:255',
            'details.*.region' => 'exclude_unless:type,information|string|max:255',
            'details.*.district' => 'exclude_unless:type,information|string|max:255|in:North,South,East,West', //Await District Requirements (rn can be Compass directions)

            // 'details.*.address' => [
            //     'exclude_unless:type,information',
            //     'sometimes',
            //     'string',
            //     'max:500',
            // ],

            'details.*.latitude' => [
                'exclude_unless:type,information',
                'sometimes',
                'numeric',
                'between:-90,90',
            ],

            'details.*.longitude' => [
                'exclude_unless:type,information',
                'sometimes',
                'numeric',
                'between:-180,180',
            ],

            'details.*.image' => [
                'exclude_unless:type,information',
                'sometimes',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png',
                'max:2048',
            ],

            'details.*.school_head' => [
                'exclude_unless:type,information',
                'nullable',
                'string',
                'unique:users,name',
                'max:255'
            ],

            'details.*.position' => [
                'exclude_unless:type,information',
                'nullable',
                'string',
                'in:Principal I,Principal II,Principal III,Principal IV'
            ],

            'details.*.email' => [
                'exclude_unless:type,information',
                'string',
                'email',
                'max:255',
                'unique:users,email'
            ],

            'details.*.phone_number' => [
                'exclude_unless:type,information',
                'string',
                'string',
                'max:15'
            ],


        ];
    }
    protected function prepareForValidation(): void
            {
                if($this['type'] === 'information'){
                    $user = $this->user();
                    $school = School::query()->where('id', $user->school_id)->first();

                    $data = $this->all();
                    if($data['details'][0]['school_name'] === $school->school_name){
                        unset($data['details'][0]['school_name']);
                    }
                    if($data['details'][0]['school_code'] === $user->name){
                        unset($data['details'][0]['school_code']);
                    }
                    if($data['details'][0]['email'] === $user->email){
                        unset($data['details'][0]['email']);
                    }
                    if($data['details'][0]['school_head'] === $user->name){
                        unset($data['details'][0]['school_head']);
                    }

                    $this->replace($data);
                }
            }

    public function messages(): array
    {
        return [
            'details.*.position.in' =>
                'Invalid position selected. Valid positions are: Principal I,Principal II,Principal III,Principal IV.',
        ];
    }
}
