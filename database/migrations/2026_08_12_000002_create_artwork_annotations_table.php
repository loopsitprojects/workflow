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
        Schema::create('artwork_annotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artwork_review_id')->constrained('artwork_reviews')->onDelete('cascade');
            $table->enum('type', ['pin', 'drawing', 'text'])->default('pin');
            // Position stored as percentage of image dimensions for responsiveness
            $table->float('x_percent')->nullable();
            $table->float('y_percent')->nullable();
            // Fabric.js serialised JSON for drawings; plain text for pins/text
            $table->longText('content')->nullable();
            $table->string('color', 20)->default('#ef4444');
            $table->unsignedInteger('pin_number')->nullable();
            // Team resolution tracking
            $table->boolean('is_resolved')->default(false);
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artwork_annotations');
    }
};
