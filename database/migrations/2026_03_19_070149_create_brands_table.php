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
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo_url')->nullable();
            $table->string('location')->nullable();
            $table->string('description')->nullable();
            $table->integer('active_projects')->default(0);
            $table->integer('total_members')->default(0);
            $table->decimal('overall_progress', 5, 2)->default(0);
            $table->string('health_score')->default('Stable');
            $table->string('milestones_met')->nullable();
            $table->string('revenue_impact')->nullable();
            $table->string('current_lead')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
