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
            $table->string('approval_stage')->nullable()->after('status');
            $table->text('final_designs')->nullable()->after('reference');
            $table->integer('revisions')->default(0)->after('final_designs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['approval_stage', 'final_designs', 'revisions']);
        });
    }
};
