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
        Schema::table('deliverable_approvals', function (Blueprint $table) {
            $table->decimal('hours_spent', 8, 2)->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deliverable_approvals', function (Blueprint $table) {
            $table->dropColumn('hours_spent');
        });
    }
};
