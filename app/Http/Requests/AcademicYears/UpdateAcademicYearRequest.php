<?php

namespace App\Http\Requests\AcademicYears;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Support\Carbon;

class UpdateAcademicYearRequest extends FormRequest
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
            'start_date' => 'sometimes|date|before:end_date|unique:academic_years,start_date',
            'end_date' => 'sometimes|date|after:start_date|unique:academic_years,end_date',
        ];
    }
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator){
            $startComparison = Carbon::parse($this->start_date)->format('Y') + 1;
            $end = Carbon::parse($this->end_date)->format('Y');
                if ($startComparison != $end){
                    $validator->errors()->add('end_date', 'You must keep the longevity of the school year within 1 year');   
                }
        });
    }
}
