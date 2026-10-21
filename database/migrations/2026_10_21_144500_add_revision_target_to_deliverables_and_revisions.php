<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliverables', function (Blueprint $table) {
            $table->string('revision_target')->nullable()->after('revision_deadline');
        });

        Schema::table('deliverable_revisions', function (Blueprint $table) {
            $table->string('target')->nullable()->after('revision_deadline');
        });
    }

    public function down(): void
    {
        Schema::table('deliverables', function (Blueprint $table) {
            $table->dropColumn('revision_target');
        });

        Schema::table('deliverable_revisions', function (Blueprint $table) {
            $table->dropColumn('target');
        });
    }
};
