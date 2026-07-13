<?php

namespace App\Observers\Observers;

use App\Models\EnrollmentData;
use App\Models\EnrollmentDataDraft;
use App\Models\ResourceDataDraft;
use App\Models\ResourceData;
use App\Models\Submission;
use App\Events\PublicEnrollmentTotalsChanged;
use App\Events\PublicResourceTotalsChanged;
use App\Helpers\calculateTotal;
use App\Models\KpiData;

class SubmissionObserver
{
    use calculateTotal;
    /**
     * Handle the Submission "created" event.
     */
    public function created(Submission $submission): void
    {
        //
    }

    /**
     * Handle the Submission "updated" event.
     */
    public function updated(Submission $submission): void
    {
        //update totals of the data
        if($submission->type === 'information' && $submission->status === 'approved'){
            $draft = $submission->schoolInformationDraft->latest()->first();
                if ($draft) {
                    $school = $submission->school;
                    $school->school_name = $draft->school_name;
                    $school->school_code = $draft->school_code;
                    $school->year_established = $draft->year_established;
                    $school->school_type_id = intval($draft->school_type_id);
                    $school->address = $draft->address;
                    $school->district = $draft->district;
                    $school->latitude = $draft->latitude;
                    $school->longitude = $draft->longitude;
                    if ($draft->image) {
                        $school->image = $draft->image;
                    }
                    $school->save();
                }
        }

        if($submission->type === 'enrollment' && $submission->status === 'approved'){
            $this->applyEnrollmentDraft($submission);
        }
        if($submission->type === 'resource' && $submission->status === 'approved'){
            $this->applyResourceDraft($submission);
        }
    }

    private function applyEnrollmentDraft(Submission $submission)
    {
        foreach ($submission->enrollmentDraft as $draft) {

            EnrollmentData::query()
                ->where('academic_year_id', $submission->academic_year_id)
                ->where('school_id', $submission->school_id)
                ->where('grade_level', $draft->grade_level)
                ->update([
                    'male_count' => $draft->male_count,
                    'female_count' => $draft->female_count,
                ]);
        }

        $models = KpiData::query()->where('academic_year_id', $submission->academic_year_id)->get();
        foreach ($models as $model) {
                $total = $this->calculateTotal($model['male'], $model['female'], $model->academic_year_id, true);

                $model->update([
                    'total'  => $total
                ]);
            }
        PublicEnrollmentTotalsChanged::dispatch($submission->academic_year_id);

    }

    private function applyResourceDraft(Submission $submission)
    {
        foreach ($submission->resourceDraft as $draft) {

            ResourceData::query()
                ->where('academic_year_id', $submission->academic_year_id)
                ->where('school_id', $submission->school_id)
                ->where('resource_name', $draft->resource_name)
                ->update([
                    'inventory' => $draft->inventory,
                    'requirement' => $draft->requirement,
                ]);
        }

        PublicResourceTotalsChanged::dispatch($submission->academic_year_id);
    }

    /**
     * Handle the Submission "restored" event.
     */
    public function restored(Submission $submission): void
    {
        //
    }

    /**
     * Handle the Submission "force deleted" event.
     */
    public function forceDeleted(Submission $submission): void
    {
        //
    }
}
