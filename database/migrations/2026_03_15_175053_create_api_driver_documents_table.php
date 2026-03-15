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
        Schema::create('api_driver_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chauffeur_id')->constrained('users')->cascadeOnDelete();
            $table->string('type');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('path');
            $table->text('notes')->nullable();
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamp('reviewed_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_driver_documents');
    }
};
