<?php

namespace App\Http\Requests\KPI;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        ],
        'items.*.academic_year' => [
            'required',
            'string',
            Rule::exists('academic_years', 'name')->where(function ($query) {
                $query->where('status', 'default'); // only default status allowed
            }),
        ],
        'items.*.school_type' => ['required', 'string', 'in:elementary,secondary'],

        // Numeric fields: min 0, max 100, 1 decimal
        'items.*.male' => ['required', 'numeric', 'min:0', 'max:100', 'regex:/^\d+(\.\d)?$/'],
        'items.*.female' => ['required', 'numeric', 'min:0', 'max:100', 'regex:/^\d+(\.\d)?$/'],
        'items.*.total' => ['required', 'numeric', 'min:0', 'max:100', 'regex:/^\d+(\.\d)?$/'],
    ];
    }
}
