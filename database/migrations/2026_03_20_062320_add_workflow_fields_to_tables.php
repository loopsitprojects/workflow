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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('Member'); // Writer, Approver, Brand Manager, Designer, etc.
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->string('workflow_type')->default('campaign'); // retainer, campaign, pitch
            $table->foreignId('writer_id')->nullable()->constrained('users');
            $table->foreignId('approver_id')->nullable()->constrained('users');
            $table->foreignId('brand_manager_id')->nullable()->constrained('users');
            $table->foreignId('designer_id')->nullable()->constrained('users');
            $table->string('sub_type')->nullable(); // Retainer Job, Campaign, Pitch, Project
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->string('task_type')->nullable(); // Outline, Concept, Caption, Post copy, KV, etc.
            $table->integer('progress_percent')->default(0); // 0, 20, 40, 60, 80, 100
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['writer_id']);
            $table->dropForeign(['approver_id']);
            $table->dropForeign(['brand_manager_id']);
            $table->dropForeign(['designer_id']);
            $table->dropColumn(['workflow_type', 'writer_id', 'approver_id', 'brand_manager_id', 'designer_id', 'sub_type']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['task_type', 'progress_percent']);
        });
    }
};
