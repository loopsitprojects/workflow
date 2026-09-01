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
            $table->unsignedInteger('artwork_index')->default(0)->after('artwork_review_id');
            $table->text('image_url')->nullable()->after('artwork_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artwork_annotations', function (Blueprint $table) {
            $table->dropColumn(['artwork_index', 'image_url']);
        });
    }
};
