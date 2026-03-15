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
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            $table->foreignUuid('utilisateur_id')->constrained('utilisateurs', 'utilisateur_id')->onDelete('cascade');
            $table->string('token', 512)->unique();
            $table->timestamp('dateCreation')->useCurrent();
            $table->timestamp('dateExpiration');
            $table->boolean('estActif')->default(TRUE);
            $table->string('adresseIP',45);
            $table->string('appareil',100);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
    }
};
