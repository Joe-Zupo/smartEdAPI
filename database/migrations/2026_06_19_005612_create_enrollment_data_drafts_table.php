<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Submission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('enrollment_data_drafts', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Submission::class)->constrained()->onDelete('cascade');

            $table->string('grade_level');
            $table->smallInteger('male_count');
            $table->smallInteger('female_count');
            $table->smallInteger('total_count')->storedAs('male_count + female_count');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_data_drafts');
    }
};
