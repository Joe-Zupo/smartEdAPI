<?php

namespace Database\Seeders;

use App\Models\Submission;
use App\Models\Notifications;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $submissions = Submission::with(['school', 'academicYear', 'user', 'comments'])->get();

        foreach ($submissions as $submission) {
            $typeLabel = ucfirst($submission->type);
            $yearName = $submission->academicYear->name;
            $schoolName = $submission->school->name;
            $userName = $submission->user->name;
            $subNum = $submission->submission_number;

            Notifications::create([
                'submission_id' => $submission->id,
                'title' => "New Submission from {$schoolName}",
                'message' => "{$typeLabel} data for {$yearName} has been submitted by {$userName} and requires validation.",
                'created_at' => $submission->created_at,
            ]);

            // Add the school-facing notification mirroring controller behavior
            Notifications::create([
                'submission_id' => $submission->id,
                'title' => 'Pending Review',
                'message' => "Your submission {$subNum} has been successfully submitted and is awaiting approval.",
                'created_at' => $submission->created_at,
            ]);

            if ($submission->status === 'approved') {
                Notifications::create([
                    'submission_id' => $submission->id,
                    'title' => 'Approved Submission',
                    'message' => "Your submission for {$subNum} has been approved.",
                    'created_at' => $submission->updated_at,
                ]);
            }

            if ($submission->status === 'returned') {
                $latestComment = $submission->comments()->latest()->first();
                $reason = $latestComment ? $latestComment->comment : 'Please review data.';

                $reason = rtrim($reason, '.');

                Notifications::create([
                    'submission_id' => $submission->id,
                    'title' => 'Submission Returned',
                    'message' => "Your submission for {$subNum} has been returned. Reason: {$reason}. Please review and resubmit.",
                    'created_at' => $submission->updated_at,
                ]);
            }
        }
    }
}
