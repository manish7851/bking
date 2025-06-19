<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGpsTrackingColumnsToBusesTable extends Migration
{
    /**
     * Run the migrations to add GPS tracking columns to the buses table.
     */
    public function up(): void
    {
        Schema::table('buses', function (Blueprint $table) {
            if (!Schema::hasColumn('buses', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable();
            }
            if (!Schema::hasColumn('buses', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable();
            }
            if (!Schema::hasColumn('buses', 'speed')) {
                $table->decimal('speed', 8, 2)->nullable()->comment('Speed in km/h');
            }
            if (!Schema::hasColumn('buses', 'heading')) {
                $table->decimal('heading', 5, 2)->nullable()->comment('Direction in degrees');
            }
            if (!Schema::hasColumn('buses', 'last_tracked_at')) {
                $table->timestamp('last_tracked_at')->nullable();
            }
            if (!Schema::hasColumn('buses', 'tracking_enabled')) {
                $table->boolean('tracking_enabled')->default(false);
            }
            if (!Schema::hasColumn('buses', 'status')) {
                $table->string('status', 20)->nullable()->default('offline')->comment('offline, moving, stopped');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buses', function (Blueprint $table) {
            $table->dropColumn([
                'latitude',
                'longitude',
                'speed',
                'heading',
                'last_tracked_at',
                'tracking_enabled',
                'status'
            ]);
        });
    }
}
