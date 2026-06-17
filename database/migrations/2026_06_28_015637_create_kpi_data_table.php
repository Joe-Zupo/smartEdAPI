<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kpi_data', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('kpi_id')->constrained('kpi_rate_data')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();

            // Data columns
            $table->decimal('male', 5, 2)->default(0);
            $table->decimal('female', 5, 2)->default(0);
            $table->decimal('total', 5, 2)->default(0);

            //Future Columns for later scaling
            $table->integer('male_population')
            ->nullable();

             $table->integer('female_population')
            ->nullable();

            // Enum for school type
            $table->enum('school_type', ['Elementary','Integrated School','Junior High School','Junior High School with SHS','Standalone SHS','Science High School','ALS']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_data');
    }
};
