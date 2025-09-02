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
        if (!Schema::hasColumn('bookings', 'bus_number')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->string('bus_number')->nullable()->after('bus_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('bookings', 'bus_number')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('bus_number');
            });
        }
    }
};
