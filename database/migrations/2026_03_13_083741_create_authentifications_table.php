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
        Schema::create('authentifications', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            $table->foreignUuid('utilisateur_id')->constrained('utilisateurs', 'utilisateur_id')->onDelete('cascade')->unique();
            $table->integer('tentativesEchouees')->default(0);
            $table->integer('maxTentatives')->default(5);
            $table->timestamp('dateVerrouillage')->nullable();
            $table->string('codeOTP',6)->nullable();
            $table->timestamp('dateExpirationOTP')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authentifications');
    }
};
