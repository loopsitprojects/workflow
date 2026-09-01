<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = DB::select("SHOW INDEXES FROM artwork_reviews WHERE Key_name = 'artwork_reviews_token_unique'");
        if (!empty($indexes)) {
            Schema::table('artwork_reviews', function (Blueprint $table) {
                $table->dropUnique('artwork_reviews_token_unique');
            });
        }

        $tokenIndexes = DB::select("SHOW INDEXES FROM artwork_reviews WHERE Key_name = 'artwork_reviews_token_index'");
        if (empty($tokenIndexes)) {
            Schema::table('artwork_reviews', function (Blueprint $table) {
                $table->index('token');
            });
        }
    }

    public function down(): void
    {
        Schema::table('artwork_reviews', function (Blueprint $table) {
            $table->dropIndex(['token']);
            $table->unique('token');
        });
    }
};
