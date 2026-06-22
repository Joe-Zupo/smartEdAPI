<?php

namespace App\Http\Requests\AcademicYears;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use App\Models\AcademicYear;

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
            'start_date' => 'sometimes|date|before:end_date|unique:academic_years,start_date|'. Rule::after(2020-01-01),
            'end_date' => 'sometimes|date|after:start_date|unique:academic_years,end_date',
        ];
    }
    public function withValidator(Validator $validator): void
    {
         $validator->after(function ($validator){
            $start = Carbon::parse($this->start_date);
            $end = Carbon::parse($this->end_date);

                if ($end->lte($start)) {
                    $validator->errors()->add(
                        'end_date',
                        'End date must be after start date.'
                    );
                }
            
            $duration = $start->diffInDays($end);

                if ($duration > 366) {
                    $validator->errors()->add(
                        'end_date',
                        'Academic year cannot exceed 1 year.'
                    );
                }
            
            $overlap = AcademicYear::query()
                ->when($this->route('academic_year'), function ($q) {
                    $q->where('id', '!=', $this->route('academic_year')->id);
                })
                ->where(function ($query) use ($start, $end) {

                    $query->whereDate('start_date', '<=', $end)
                        ->whereDate('end_date', '>=', $start);

                })
                ->exists();

            if ($overlap){
                $validator->errors()->add(
                    'start_date',
                    'This academic year overlaps with an existing academic year.'
                );
            }
        });
        
    }
}
