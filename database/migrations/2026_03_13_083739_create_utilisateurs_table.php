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
        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->uuid('utilisateur_id')->primary()->default(DB::raw('(UUID())'));
            $table->string('nom',100);
            $table->string('prenom',100);
            $table->string('email',150);
            $table->string('telephone',20)->unique();
            $table->string('mot_de_passe',255);
            $table->enum('role',['Chauffeur','Client','Admin']);
            $table->enum('statut',['libre','deconnecté','en course'])->default('deconnecté');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utilisateurs');
    }
};
