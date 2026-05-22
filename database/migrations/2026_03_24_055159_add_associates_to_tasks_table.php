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
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('writer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('brand_manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('coordinator_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('designer_id')->nullable()->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['writer_id']);
            $table->dropForeign(['brand_manager_id']);
            $table->dropForeign(['coordinator_id']);
            $table->dropForeign(['designer_id']);
            $table->dropColumn(['writer_id', 'brand_manager_id', 'coordinator_id', 'designer_id']);
        });
    }
};
