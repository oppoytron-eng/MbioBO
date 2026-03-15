<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            $table->foreignUuid('client_id')->constrained('clients')->ondelete('cascade');
            $table->foreignUuid('chauffeur_id')->constrained('chauffeurs')->onDelete('cascade');
            $table->enum('statut',['En ATTENTE','Hors ligne','En ligne','En course'])->default('En ATTENTE');
            $table->string('adresse_depart',255);
            $table->decimal('lat_depart',10,8);
            $table->decimal('lng_depart',11,8);
            $table->string('adresse_arrivee',255);
            $table->decimal('lat_arrivee',10,8);
            $table->decimal('lng_arrivee',11,8);
            $table->decimal('distance_km',8,2);
            $table->decimal('prix_estime',10,2);
            $table->decimal('prix_final',10,2);
            $table->boolean('est_actif')->default(false);
            $table->enum('modePaiement',['CASH','MOMO_MTN','ORANGE_MONEY']);
            $table->timestamp('demande_le')->useCurrent();
            $table->timestamp('termine_le');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
