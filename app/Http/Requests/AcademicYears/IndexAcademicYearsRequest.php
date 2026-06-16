<?php

namespace App\Http\Requests\AcademicYears;

use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\AcademicYear;
use Illuminate\Foundation\Http\FormRequest;

class IndexAcademicYearsRequest extends FormRequest
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
            'status' => 'string|in:active,default,upcoming,archived',
            'academic_year' => 'string|exists:academic_years,academic_year|nullable',
            'withoutUA' => 'in:true,false|nullable',
            'per_page' => 'integer',
            'page' => 'integer'
        ];
    }
        public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $user = $this->user();

            if (!$user->hasRole('System Admin')) {

                if ($this->filled('status')) {

                    $allowedStatuses = [
                        'active',
                        'default',
                    ];

                    if (!in_array($this->status, $allowedStatuses)) {

                        $validator->errors()->add(
                            'status',
                            'You may only filter by active or default academic years.'
                        );
                    }
                }

                if ($this->filled('academic_year')) {

                    $exists = AcademicYear::query()->where('academic_year', $this->academic_year)
                        ->whereIn('status', [
                            'active',
                            'default',
                        ])
                        ->exists();

                    if (!$exists) {

                        $validator->errors()->add(
                            'academic_year',
                            'You may only filter by active or default academic years.'
                        );
                    }
                }
            }
        });
    }
}
