<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationColumnsToBusesTable extends Migration
{
    /**
     * Run the migrations.
     */    public function up(): void
    {
        // Migration has been superseded by 2025_05_22_000010_add_gps_tracking_columns_to_buses_table
        // Leaving this empty to avoid conflicts
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buses', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'last_tracked_at']);
        });
    }
}
