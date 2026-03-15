<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chauffeurs', function (Blueprint $table) {
            $table->uuid('id')->primary();             // ✅ UUID géré par Laravel, pas MySQL
            $table->uuid('utilisateur_id');
            $table->string('numero_permis', 50)->unique();
            $table->enum('statut', ['Hors ligne', 'En ligne', 'En course'])->default('Hors ligne');
            $table->enum('statut_operationnel', ['actif', 'suspendu', 'inactif'])->default('actif');
            $table->decimal('note_moyenne', 3, 2)->default(0);  // ✅ decimal (pas decimale)
            $table->integer('nb_courses')->default(0);
            $table->decimal('solde_total', 10, 2)->default(0);
            $table->decimal('lat_actuelle', 10, 8)->nullable();
            $table->decimal('lng_actuelle', 11, 8)->nullable();
            $table->boolean('est_actif')->default(true);
            $table->enum('etat_documents', ['pending', 'valide', 'rejete'])->default('pending');
            $table->timestamp('created_at')->useCurrent();      // ✅ sur Blueprint, pas Collection
            $table->softDeletes();

            $table->foreign('utilisateur_id')->references('utilisateur_id')->on('utilisateurs')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chauffeurs');
    }
};
