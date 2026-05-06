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
        Schema::create('classes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();
            $table->uuid('app_id')->index();
            $table->string('code');
            $table->string('name');
            $table->string('grade')->nullable();
            $table->string('section')->nullable();
            $table->string('academic_year');
            $table->integer('capacity')->default(15);
            $table->uuid('teacher_id')->nullable(); // Bare UUID, no FK
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'app_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
