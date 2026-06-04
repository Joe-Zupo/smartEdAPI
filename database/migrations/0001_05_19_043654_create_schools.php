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
            $table->id();
            $table->string('school_name')->unique();
            $table->string('school_code')->unique();
            $table->year('year_established');
            $table->foreignId('school_type_id')->constrained('school_types')->cascadeOnDelete();
            $table->string('district');
            $table->string('school_head')->unique()->nullable(); //nullable for seeder
            $table->string('position');
            $table->string('phone_number')->nullable(); //nullable for seeder 
            $table->string('head_email')->unique()->nullable(); //nullable for seeder
            $table->text('address')->nullable();
            $table->string('region');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
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
