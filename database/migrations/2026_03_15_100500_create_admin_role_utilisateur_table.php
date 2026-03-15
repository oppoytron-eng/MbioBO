<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_role_utilisateur', function (Blueprint $table) {
            $table->uuid('admin_role_id');
            $table->uuid('utilisateur_id');
            $table->primary(['admin_role_id', 'utilisateur_id']);
            $table->foreign('admin_role_id')->references('id')->on('admin_roles')->cascadeOnDelete();
            $table->foreign('utilisateur_id')->references('utilisateur_id')->on('utilisateurs')->cascadeOnDelete();
            $table->timestamp('assigned_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_role_utilisateur');
    }
};
