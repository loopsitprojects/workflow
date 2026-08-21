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
        Schema::table('artwork_annotations', function (Blueprint $table) {
            $table->text('response_text')->nullable()->after('content');
            $table->foreignId('responded_by')->nullable()->after('response_text')->constrained('users')->onDelete('set null');
            $table->timestamp('responded_at')->nullable()->after('responded_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artwork_annotations', function (Blueprint $table) {
            $table->dropForeign(['responded_by']);
            $table->dropColumn(['response_text', 'responded_by', 'responded_at']);
        });
    }
};
