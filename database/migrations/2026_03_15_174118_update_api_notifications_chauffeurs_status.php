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
        Schema::table('api_notifications_chauffeurs', function (Blueprint $table) {
            $table->boolean('countdown_active')->default(false)->after('expire_le');
            $table->timestamp('countdown_started_at')->nullable()->after('countdown_active');
            $table->timestamp('countdown_expired_at')->nullable()->after('countdown_started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_notifications_chauffeurs', function (Blueprint $table) {
            $table->dropColumn([
                'countdown_active',
                'countdown_started_at',
                'countdown_expired_at',
            ]);
        });
    }
};
