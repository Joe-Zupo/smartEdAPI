<?php

namespace App\Http\Requests\KPI;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateKpiDataRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
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

            'items.*.id' => ['required', 'integer', 'exists:kpi_data,id'],

            // Numeric fields: min 0, max 100, up to 1 decimal
            'items.*.male' => ['required', 'numeric', 'min:0', 'max:100', 'regex:/^\d+(\.\d)?$/'],
            'items.*.female' => ['required', 'numeric', 'min:0', 'max:100', 'regex:/^\d+(\.\d)?$/'],
            'items.*.total' => ['required', 'numeric', 'min:0', 'max:100', 'regex:/^\d+(\.\d)?$/'],
        ];
    }
    }
}
