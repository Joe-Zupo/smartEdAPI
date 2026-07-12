<?php

namespace App\Http\Resources\KPIDataService;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KPITrendResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
                return [
            'data' => [

                'kpi_trends' => collect($this['data']['kpi_trends'])
                    ->map(function ($trend) {

                        return [

                            'id' => $trend['id'],

                            'kpi_id' => $trend['kpi_id'],

                            'kpi_rate' => $trend['kpi_rate'],

                            'academic_year_id' => $trend['academic_year_id'],

                            'academic_year' => $trend['academic_year'],

                            'male' => $trend['male'],

                            'female' => $trend['female'],

                            'total' => $trend['total'],

                            'school_type' => $trend['school_type'],

                        ];
                    })
                    ->values(),

                'kpi_trends_total' => collect($this['data']['kpi_trends_total'])
                    ->map(function ($trend) {

                        return [

                            'kpi_id' => $trend['kpi_id'],

                            'kpi_rate' => $trend['kpi_rate'],

                            'male_five_year_avg' => $trend['male_five_year_avg'],

                            'female_five_year_avg' => $trend['female_five_year_avg'],

                            'total_five_year_avg' => $trend['total_five_year_avg'],

                        ];
                    })
                    ->values(),

            ],
        ];
    }
}
