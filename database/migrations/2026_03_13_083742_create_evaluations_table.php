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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            $table->foreignUuid('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignUuid('evaluateur_id')->constrained('utilisateurs', 'utilisateur_id')->onDelete('cascade');
            $table->foreignUuid('evaluer_id')->constrained('utilisateurs', 'utilisateur_id');
            $table->integer('note')->checkBetween(1,5);
            $table->text('commentaire')->nullable();
            $table->enum('evaluateur_role',['CHAUFFEUR','PASSAGER']);
            $table->timestamp('dateHeure')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
