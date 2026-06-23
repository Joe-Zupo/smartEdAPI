<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Comment;
use App\Models\Submission;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::role('Division Admin')->first() ?? User::first();

        $submissions = Submission::inRandomOrder()->take(5)->get();

        $messages = [
            'Please double check the Grade 1 totals.',
            'Discrepancy found in teacher inventory.',
            'Approved. Good job.',
            'Kindly update the male count for Grade 6.',
            'Why is the classroom inventory so low?'
        ];

        foreach ($submissions as $submission) {
            Comment::create([
                'submission_id' => $submission->id,
                'user_id' => $admin->id,
                'comment' => $messages[array_rand($messages)],
                'created_at' => now(),
            ]);
        }
    }
}
