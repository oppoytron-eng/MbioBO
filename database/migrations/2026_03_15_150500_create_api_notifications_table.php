<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('api_courses')->onDelete('cascade');
            $table->string('type');
            $table->text('message');
            $table->enum('statut', ['envoyee', 'vue', 'expiree'])->default('envoyee');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_notifications');
    }
};
