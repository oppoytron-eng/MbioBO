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
        if (Schema::hasColumn('clients', 'votre_colonne')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropColumn('votre_colonne');
            });
        }

        Schema::table('clients', function (Blueprint $table) {
            $table->boolean('est_actif')->default(true)->after('nb_courses');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropTimestamps();
            $table->dropColumn('est_actif');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->timestamp('votre_colonne')->useCurrent();
        });
    }
};
