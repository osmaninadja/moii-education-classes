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
        Schema::create('class_enrollments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('app_id')->index();
            $table->uuid('class_id');
            $table->uuid('student_id')->index(); // Bare UUID, no FK
            $table->enum('status', ['active', 'dropped', 'completed'])->default('active');
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamp('unenrolled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
            
            $table->unique(['class_id', 'student_id'], 'class_student_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_enrollments');
    }
};
