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
        Schema::create('school_information_drafts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();

            $table->string('name')->nullable();
            $table->string('code')->nullable();
            $table->year('year_established')->nullable();
            $table->foreignId('school_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('address', 500)->nullable();
            $table->string('district')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('image')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_information_drafts');
    }
};
