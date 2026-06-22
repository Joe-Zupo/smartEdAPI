<?php

namespace App\Http\Requests\KPI;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\KpiData;
use App\Models\KpiRateData;
use App\Models\AcademicYear;
use App\Models\SchoolType;

class StoreKpiDataRequest extends FormRequest
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
            'items' => ['required', 'array', 'min:1'],

            'items.*.kpi_rate_name' => [
                'required',
                'string',
                Rule::exists('kpi_rate_data', 'name'), // ensures the KPI name exists
                Rule::in(KpiRateData::pluck('name')->toArray()),
            ],
            'items.*.academic_year' => [
                'required',
                'string',
                Rule::exists('academic_years', 'academic_year')->where(function ($query) {
                    $query->where('status', 'default'); // only default status allowed
                }),
                Rule::in(AcademicYear::whereIn('status', ['active', 'default'])->pluck('academic_year')->toArray())
            ],
            'items.*.school_type' => ['required', 'string', Rule::in(SchoolType::pluck('name')->toArray())],

            // Numeric fields: min 0, max 100, 1 decimal
            'items.*.male' => ['required', 'numeric', 'min:0', 'max:100', 'regex:/^\d+(\.\d)?$/'],
            'items.*.female' => ['required', 'numeric', 'min:0', 'max:100', 'regex:/^\d+(\.\d)?$/'],
        ];
    }
        public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->validated();

            foreach ($data['items'] as $index => $item) {

                // ✅ Block duplicate KPI per academic_year + school_type
                // IDs will be converted in controller, so check by names
                $academicYear = AcademicYear::query()->where('academic_year', $item['academic_year'])
                    ->where('status', 'default')
                    ->first();
                $kpi = KpiRateData::query()->where('name', $item['kpi_rate_name'])->first();

                if ($academicYear && $kpi) {
                    $exists = KpiData::query()->where('kpi_id', $kpi->id)
                        ->where('academic_year_id', $academicYear->id)
                        ->where('school_type', $item['school_type'])
                        ->exists();

                    if ($exists) {
                        $validator->errors()->add(
                            "items.$index.kpi_rate_name",
                            'This KPI already exists for the selected academic year and school type.'
                        );
                    }
                }
            }
        });
    }
}
