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
        Schema::table('courses', function (Blueprint $table) {
            $table->string('adresse_depart')->nullable()->change();
            $table->string('adresse_arrivee')->nullable()->change();
            $table->decimal('distance_km', 8, 2)->nullable()->change();
            $table->decimal('prix_final', 10, 2)->nullable()->change();
            $table->enum('modePaiement', ['CASH', 'MOMO_MTN', 'ORANGE_MONEY'])->nullable()->change();
            $table->timestamp('termine_le')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            //
        });
    }
};
