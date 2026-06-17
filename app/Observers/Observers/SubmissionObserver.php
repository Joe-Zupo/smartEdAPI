<?php

namespace App\Observers\Observers;

use App\Models\EnrollmentData;
use App\Models\ResourceData;
use App\Models\Submission;

class SubmissionObserver
{
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
        if($submission->type === 'information'){
            $draft = $submission->schoolInformationDraft->latest()->first();
                if ($draft) {
                    $school = $submission->school;
                    $school->name = $draft->name;
                    $school->code = $draft->code;
                    $school->year_established = $draft->year_established;
                    $school->school_type_id = $draft->school_type_id;
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
        if($submission->type === 'enrollment'){
            $enrollments = EnrollmentData::query()->where('submission_id', $submission->id)->get();

            foreach($enrollments as $enrollment){
                $enrollment->update();
            }
        }
        if($submission->type === 'resource'){
            $resources = ResourceData::query()->where('submission_id', $submission->id)->get();

            foreach($resources as $resource){
                $resource->update();
            }
        }
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
