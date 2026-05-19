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
        Schema::create('schools', function (Blueprint $table) {
            //School Information
            $table->id();
            $table->string('school_name');
            $table->string('school_code');
            $table->year('year_established');
            $table->foreignID('school_type_id')->constrained('school_types')->cascadeOnDelete();
            
            //location
            $table->string('address');
            $table->string('district');
            $table->decimal('longitude', 8, 2);
            $table->decimal('latitude', 8, 2);

            //other
            $table->string('image')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
