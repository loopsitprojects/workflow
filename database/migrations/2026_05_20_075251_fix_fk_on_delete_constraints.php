<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function foreignKeyExists(string $table, string $key): bool
    {
        $col = str_replace([$table . '_', '_foreign'], '', $key);
        $foreignKeys = \Illuminate\Support\Facades\Schema::getForeignKeys($table);
        foreach ($foreignKeys as $fk) {
            if ($fk['name'] === $key || (isset($fk['columns']) && in_array($col, $fk['columns']))) {
                return true;
            }
        }
        return false;
    }

    public function up(): void
    {
        // Fix projects: role assignee FKs had no onDelete — set to null when user deleted
        Schema::table('projects', function (Blueprint $table) {
            foreach (['writer_id', 'approver_id', 'brand_manager_id', 'designer_id'] as $col) {
                $fk = "projects_{$col}_foreign";
                if ($this->foreignKeyExists('projects', $fk)) {
                    $table->dropForeign([$col]);
                }
                $table->foreign($col)->references('id')->on('users')->nullOnDelete();
            }
        });

        // Fix deliverable_revisions: fixed_by_user_id had no onDelete — set to null when user deleted
        Schema::table('deliverable_revisions', function (Blueprint $table) {
            if (Schema::getConnection()->getDriverName() === 'sqlite') {
                $table->dropForeign(['fixed_by_user_id']);
            } else {
                if ($this->foreignKeyExists('deliverable_revisions', 'task_revisions_fixed_by_user_id_foreign')) {
                    $table->dropForeign('task_revisions_fixed_by_user_id_foreign');
                } elseif ($this->foreignKeyExists('deliverable_revisions', 'deliverable_revisions_fixed_by_user_id_foreign')) {
                    $table->dropForeign('deliverable_revisions_fixed_by_user_id_foreign');
                }
            }
            $table->foreign('fixed_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['writer_id']);
            $table->dropForeign(['approver_id']);
            $table->dropForeign(['brand_manager_id']);
            $table->dropForeign(['designer_id']);

            $table->foreign('writer_id')->references('id')->on('users');
            $table->foreign('approver_id')->references('id')->on('users');
            $table->foreign('brand_manager_id')->references('id')->on('users');
            $table->foreign('designer_id')->references('id')->on('users');
        });

        Schema::table('deliverable_revisions', function (Blueprint $table) {
            $table->dropForeign(['fixed_by_user_id']);
            $table->foreign('fixed_by_user_id')->references('id')->on('users');
        });
    }
};
