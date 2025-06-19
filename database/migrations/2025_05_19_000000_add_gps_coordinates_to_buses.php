<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Columns already exist, so do nothing to avoid duplicate column error
        // Schema::table('buses', function (Blueprint $table) {
        //     $table->decimal('latitude', 10, 7)->nullable();
        //     $table->decimal('longitude', 10, 7)->nullable();
        //     $table->timestamp('last_tracked_at')->nullable();
        //     $table->boolean('tracking_enabled')->default(false);
        // });
    }

    public function down()
    {
        // Do nothing to avoid dropping columns that may be in use
        // Schema::table('buses', function (Blueprint $table) {
        //     $table->dropColumn(['latitude', 'longitude', 'last_tracked_at', 'tracking_enabled']);
        // });
    }
};
