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
        Schema::create('api_course_tracks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('api_courses')->cascadeOnDelete();
            $table->foreignId('chauffeur_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('latitude_accuracy', 8, 4)->nullable();
            $table->decimal('longitude_accuracy', 8, 4)->nullable();
            $table->decimal('bearing', 8, 2)->nullable();
            $table->decimal('speed', 8, 2)->nullable();
            $table->timestamp('recorded_at')->useCurrent();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_course_tracks');
    }
};
