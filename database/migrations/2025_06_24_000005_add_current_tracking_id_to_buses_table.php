<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('buses', function (Blueprint $table) {
            // $table->unsignedBigInteger('current_tracking_id')->nullable()->after('tracking_enabled');
            // $table->foreign('current_tracking_id')->references('id')->on('bus_trackings')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('buses', function (Blueprint $table) {
            // $table->dropForeign(['current_tracking_id']);
            // $table->dropColumn('current_tracking_id');
        });
    }
};
