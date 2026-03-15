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
        Schema::table('chauffeurs', function (Blueprint $table) {
            if (! Schema::hasColumn('chauffeurs', 'est_actif')) {
                $table->boolean('est_actif')->default(true)->after('lng_actuelle');
            }

            if (! Schema::hasColumn('chauffeurs', 'statut_operationnel')) {
                $table->enum('statut_operationnel', ['actif', 'inactif', 'suspendu'])->default('actif')->after('est_actif');
            }

            if (! Schema::hasColumn('chauffeurs', 'etat_documents')) {
                $table->enum('etat_documents', ['pending', 'validated', 'rejected'])->default('pending')->after('statut_operationnel');
            }

            if (! Schema::hasColumn('chauffeurs', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chauffeurs', function (Blueprint $table) {
            if (Schema::hasColumn('chauffeurs', 'deleted_at')) {
                $table->dropSoftDeletes();
            }

            $drops = [];
            foreach (['est_actif', 'statut_operationnel', 'etat_documents'] as $column) {
                if (Schema::hasColumn('chauffeurs', $column)) {
                    $drops[] = $column;
                }
            }

            if (! empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }
};
