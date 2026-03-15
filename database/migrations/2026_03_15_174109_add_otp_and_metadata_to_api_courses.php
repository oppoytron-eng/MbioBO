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
        Schema::table('api_courses', function (Blueprint $table) {
            $table->string('ride_otp', 6)->nullable()->after('mode_paiement');
            $table->timestamp('otp_expires_at')->nullable()->after('ride_otp');
            $table->timestamp('otp_verified_at')->nullable()->after('otp_expires_at');
            $table->integer('countdown_seconds')->nullable()->after('otp_verified_at');
            $table->timestamp('countdown_started_at')->nullable()->after('countdown_seconds');
            $table->decimal('distance_meters', 10, 2)->nullable()->after('countdown_started_at');
            $table->json('client_snapshot')->nullable()->after('distance_meters');
            $table->json('metadata')->nullable()->after('client_snapshot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_courses', function (Blueprint $table) {
            $table->dropColumn([
                'ride_otp',
                'otp_expires_at',
                'otp_verified_at',
                'countdown_seconds',
                'countdown_started_at',
                'distance_meters',
                'client_snapshot',
                'metadata',
            ]);
        });
    }
};
