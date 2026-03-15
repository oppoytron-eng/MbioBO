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
        Schema::create('positions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            $table->foreignUuid('courses_id')->constrained('courses')->onDelete('cascade');
            $table->foreignUuid('chauffeur_id')->constrained('chauffeurs')->onDelete('cascade');
            $table->decimal('latitude',10,8);
            $table->decimal('longitude',11,8);
            $table->float('vitesse')->nullable();
            $table->float('cap')->nullable();
            $table->float('precision')->nullable();
            $table->timestamp('timestamp')->useCurrent()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
