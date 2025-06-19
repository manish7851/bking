<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to remove tracking-related columns from buses table.
     */
    public function up(): void
    {
        Schema::table('buses', function (Blueprint $table) {
            // Remove all tracking-related columns
            if (Schema::hasColumn('buses', 'latitude')) {
                $table->dropColumn('latitude');
            }
            if (Schema::hasColumn('buses', 'longitude')) {
                $table->dropColumn('longitude');
            }
            if (Schema::hasColumn('buses', 'last_tracked_at')) {
                $table->dropColumn('last_tracked_at');
            }
            if (Schema::hasColumn('buses', 'tracking_enabled')) {
                $table->dropColumn('tracking_enabled');
            }
        });
    }

    /**
     * Reverse the migrations (add back the tracking columns).
     */
    public function down(): void
    {
        Schema::table('buses', function (Blueprint $table) {
            // Add back tracking columns if needed
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamp('last_tracked_at')->nullable();
            $table->boolean('tracking_enabled')->default(false);
        });
    }
};
