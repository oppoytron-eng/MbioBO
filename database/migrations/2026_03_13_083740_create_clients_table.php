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
        Schema::create('clients', function (Blueprint $table) {
            // Correction : Utilisation de -> au lieu de .
            $table->uuid('id')->primary()->default(DB::raw('(UUID())')); 
            
            // Correction : constrained prend le NOM DE LA TABLE, pas table.id
            $table->foreignUuid('utilisateur_id')->constrained('utilisateurs', 'utilisateur_id')->onDelete('cascade');
            
            $table->decimal('note_moyenne', 3, 2)->default(0.0);
            $table->integer('nb_courses')->default(0);
            $table->timestamp('votre_colonne')->useCurrent();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
