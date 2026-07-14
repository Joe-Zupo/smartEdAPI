<?php

namespace App\Http\Requests\Submissions;

use App\Helpers\updateValidator;
use App\Models\GradeLevel;
use App\Models\School;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\SchoolType;
use App\Models\User;

class StoreSubmissionsRequest extends FormRequest
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
            'type' => ['required', 'in:enrollment,resource,information'],

            'details' => ['required', 'array', 'min:1'],

            //
            'details.*.grade_level' => [
                'exclude_unless:type,enrollment',
                'required',
                'exists:grade_levels,name',
                'distinct',
                Rule::in(GradeLevel::pluck('name')->toArray())
            ],

            'details.*.male_count' => ['exclude_unless:type,enrollment', 'required', 'integer', 'min:0'],
            'details.*.female_count' => ['exclude_unless:type,enrollment', 'required', 'integer', 'min:0'],

            //
            'details.*.resource_name' => [
                'exclude_unless:type,resource',
                'required',
                'in:Classrooms,Teachers,Seats,Learning Materials',
                'distinct'
            ],

            'details.*.inventory' => ['exclude_unless:type,resource', 'required', 'integer', 'min:0'],
            'details.*.requirement' => ['exclude_unless:type,resource', 'required', 'integer', 'min:0'],

            //
            'details.*.school_name' => [
                'exclude_unless:type,information',
                'nullable',
                'unique:schools,school_name',
                'unique:school_information_drafts,school_name',
                'string',
                'max:255',
            ],

            'details.*.school_code' => [
                'exclude_unless:type,information',
                'nullable',
                'string',
                'max:50',
                Rule::unique('schools', 'school_code')->ignore(auth()->user()->school_id),
            ],

            'details.*.year_established' => [
                'exclude_unless:type,information',
                'nullable',
                'digits:4',
                'integer',
            ],

            'details.*.school_type' => [
                'exclude_unless:type,information',
                'nullable',
                'exists:school_types,name',
                Rule::in(SchoolType::pluck('name')->toArray())
            ],

            'details.*.street' => [
                'exclude_unless:type,information',
                'required_if:type,information',
                'string',
                'max:255',
            ],

            'details.*.barangay' => [
                'exclude_unless:type,information',
                'required_if:type,information',
                'string',
                'max:255',
                'exists:barangays,name',
            ],

            'details.*.city' => [
                'exclude_unless:type,information',
                'required_if:type,information',
                'string',
                'max:255',
            ],

            'details.*.province' => [
                'exclude_unless:type,information',
                'required_if:type,information',
                'string',
                'max:255',
            ],

            'details.*.region' => [
                'exclude_unless:type,information',
                'required_if:type,information',
                'string',
                'max:255',
            ],

            'details.*.district' => [
                'exclude_unless:type,information',
                'required_if:type,information',
                'string',
                'max:255',
                'in:North,South,East,West',
            ],

            'details.*.latitude' => [
                'exclude_unless:type,information',
                'nullable',
                'numeric',
                'between:-90,90',
            ],

            'details.*.longitude' => [
                'exclude_unless:type,information',
                'nullable',
                'numeric',
                'between:-180,180',
            ],

            'details.*.image' => [
                'exclude_unless:type,information',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png',
                'max:2048',
            ],

            'details.*.school_head' => [
                'exclude_unless:type,information',
                'nullable',
                'unique:users,name',
                'string',
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
                'nullable',
                'string',
                'string',
                'max:20'
            ],
        ];
    }
    protected function prepareForValidation(): void
    {

        if ($this['type'] === 'information') {
            $user = $this->user();
            $school = School::query()->where('id', $user->school_id)->first();

            $data = $this->all();
            if ($data['details'][0]['school_name'] === $school->school_name) {
                unset($data['details'][0]['school_name']);
            }
            if ($data['details'][0]['school_code'] === $user->name) {
                unset($data['details'][0]['school_code']);
            }
            if ($data['details'][0]['email'] === $user->email) {
                unset($data['details'][0]['email']);
            }
            if ($data['details'][0]['school_head'] === $user->name) {
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
