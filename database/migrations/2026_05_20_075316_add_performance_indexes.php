<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliverables', function (Blueprint $table) {
            $table->index('project_id');
            $table->index('approval_stage');
            $table->index('parent_deliverable_id');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->index('brand_id');
            $table->index('status');
        });

        Schema::table('brand_user', function (Blueprint $table) {
            $table->unique(['brand_id', 'user_id']);
        });

        Schema::table('project_user', function (Blueprint $table) {
            $table->unique(['project_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('deliverables', function (Blueprint $table) {
            $table->dropIndex(['project_id']);
            $table->dropIndex(['approval_stage']);
            $table->dropIndex(['parent_deliverable_id']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['brand_id']);
            $table->dropIndex(['status']);
        });

        Schema::table('brand_user', function (Blueprint $table) {
            $table->dropUnique(['brand_id', 'user_id']);
        });

        Schema::table('project_user', function (Blueprint $table) {
            $table->dropUnique(['project_id', 'user_id']);
        });
    }
};
