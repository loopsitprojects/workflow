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
        Schema::create('artwork_annotation_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artwork_annotation_id')->constrained('artwork_annotations')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('comment');
            $table->timestamps();
        });

        // Migrate any legacy response_text into the new comments table
        try {
            $legacy = DB::table('artwork_annotations')
                ->whereNotNull('response_text')
                ->where('response_text', '!=', '')
                ->get();

            foreach ($legacy as $item) {
                DB::table('artwork_annotation_comments')->insert([
                    'artwork_annotation_id' => $item->id,
                    'user_id'               => $item->responded_by ?? null,
                    'comment'               => $item->response_text,
                    'created_at'            => $item->responded_at ?? now(),
                    'updated_at'            => $item->responded_at ?? now(),
                ]);
            }
        } catch (\Throwable $e) {
            // Ignore if legacy data cannot be migrated
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artwork_annotation_comments');
    }
};
