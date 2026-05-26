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
        Schema::create('division_leaderships', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('position', ['Schools Division Superintendent', 'Assistant Schools Division Superintendent']);
            //$table->string('image_path')->nullable();
            $table->boolean('is_oic')->default(false);
            $table->year('term_start');
            $table->year('term_end')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('division_leaderships');
    }
};
