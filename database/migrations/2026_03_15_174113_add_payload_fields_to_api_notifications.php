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
        Schema::table('api_notifications', function (Blueprint $table) {
            $table->json('payload')->nullable()->after('message');
            $table->decimal('distance_meters', 10, 2)->nullable()->after('payload');
            $table->string('sound')->nullable()->after('distance_meters');
            $table->boolean('countdown_active')->default(false)->after('sound');
            $table->integer('countdown_seconds')->nullable()->after('countdown_active');
            $table->json('client_info')->nullable()->after('countdown_seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_notifications', function (Blueprint $table) {
            $table->dropColumn([
                'payload',
                'distance_meters',
                'sound',
                'countdown_active',
                'countdown_seconds',
                'client_info',
            ]);
        });
    }
};
