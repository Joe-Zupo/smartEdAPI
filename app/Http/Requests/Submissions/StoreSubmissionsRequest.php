<?php

namespace App\Http\Requests\Submissions;

use App\Models\GradeLevel;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\SchoolType;

class StoreSubmissionsRequest extends FormRequest
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
                'sometimes',
                'string',
                'max:255',
            ],

            'details.*.school_code' => [
                'exclude_unless:type,information',
                'sometimes',
                'string',
                'max:50',
                Rule::unique('schools', 'school_code')->ignore(auth()->user()->school_id),
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
                Rule::in(SchoolType::pluck('name')->toArray())
            ],

            'details.*.address' => [
                'exclude_unless:type,information',
                'sometimes',
                'string',
                'max:500',
            ],

            'details.*.district' => [
                'exclude_unless:type,information',
                'sometimes',
                'string',
                'max:255',
            ],

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

        ];
    }
}
