<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\AcademicYear;
use App\Models\School;
use App\Models\Submission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('resource_data', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AcademicYear::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(School::class)->constrained()->onDelete('cascade');
            $table->enum('resource_name', ['Classrooms', 'Teachers', 'Seats', 'Learning Materials']);
            $table->mediumInteger('inventory');
            $table->mediumInteger('requirement');
            $table->mediumInteger('need')->storedAs('GREATEST(0, CAST(requirement AS SIGNED) - CAST(inventory AS SIGNED))');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resource_data');
    }
};
