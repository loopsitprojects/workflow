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
        Schema::rename('tasks', 'deliverables');
        Schema::rename('task_revisions', 'deliverable_revisions');
        Schema::rename('task_approvals', 'deliverable_approvals');

        Schema::table('deliverable_revisions', function (Blueprint $table) {
            $table->renameColumn('task_id', 'deliverable_id');
        });

        Schema::table('deliverable_approvals', function (Blueprint $table) {
            $table->renameColumn('task_id', 'deliverable_id');
        });
        
        Schema::table('deliverables', function (Blueprint $table) {
            $table->renameColumn('parent_id', 'parent_deliverable_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deliverables', function (Blueprint $table) {
            $table->renameColumn('parent_deliverable_id', 'parent_id');
        });

        Schema::table('deliverable_approvals', function (Blueprint $table) {
            $table->renameColumn('deliverable_id', 'task_id');
        });

        Schema::table('deliverable_revisions', function (Blueprint $table) {
            $table->renameColumn('deliverable_id', 'task_id');
        });

        Schema::rename('deliverable_approvals', 'task_approvals');
        Schema::rename('deliverable_revisions', 'task_revisions');
        Schema::rename('deliverables', 'tasks');
    }
};
