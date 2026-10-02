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
        Schema::table('deliverables', function (Blueprint $table) {
            $table->foreignId('reference_uploaded_by')->nullable()->after('reference_file')->constrained('users')->nullOnDelete();
            $table->foreignId('artwork_uploaded_by')->nullable()->after('final_designs_link')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deliverables', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reference_uploaded_by');
            $table->dropConstrainedForeignId('artwork_uploaded_by');
        });
    }
};
