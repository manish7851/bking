<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bus_locations', function (Blueprint $table) {
            $table->foreignId('bus_tracking_id')->nullable()->after('bus_id')->constrained('bus_trackings')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('bus_locations', function (Blueprint $table) {
            $table->dropForeign(['bus_tracking_id']);
            $table->dropColumn('bus_tracking_id');
        });
    }
};
