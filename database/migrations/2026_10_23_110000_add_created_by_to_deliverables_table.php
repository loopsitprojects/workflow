<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('deliverables', 'created_by')) {
            Schema::table('deliverables', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->after('project_id')->constrained('users')->nullOnDelete();
            });

            // Backfill existing deliverables with reference_uploaded_by, brand_manager_id, or first admin
            try {
                DB::statement("UPDATE deliverables SET created_by = COALESCE(reference_uploaded_by, artwork_uploaded_by, brand_manager_id, 1) WHERE created_by IS NULL");
            } catch (\Throwable $e) {
                // Ignore backfill errors if any
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('deliverables', 'created_by')) {
            Schema::table('deliverables', function (Blueprint $table) {
                $table->dropConstrainedForeignId('created_by');
            });
        }
    }
};
