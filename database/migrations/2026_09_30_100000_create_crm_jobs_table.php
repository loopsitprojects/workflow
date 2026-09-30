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
        Schema::create('crm_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('crm_job_id')->index();
            $table->string('brand_name')->index();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->string('title')->nullable();
            $table->date('deadline')->nullable();
            $table->string('status')->default('available')->index(); // 'available', 'assigned'
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_jobs');
    }
};
