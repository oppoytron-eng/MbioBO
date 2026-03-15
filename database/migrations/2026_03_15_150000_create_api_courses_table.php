<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('chauffeur_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('depart_latitude', 10, 7);
            $table->decimal('depart_longitude', 10, 7);
            $table->decimal('arrivee_latitude', 10, 7);
            $table->decimal('arrivee_longitude', 10, 7);
            $table->decimal('prix_estime', 10, 2);
            $table->decimal('prix_final', 10, 2)->nullable();
            $table->enum('statut', ['en_attente', 'acceptee', 'en_cours', 'terminee', 'annulee'])->default('en_attente');
            $table->boolean('est_paye')->default(false);
            $table->string('mode_paiement')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_courses');
    }
};
