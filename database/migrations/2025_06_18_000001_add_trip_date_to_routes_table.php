<?php
// Migration to add trip_date to routes table
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('routes', function (Blueprint $table) {
            // $table->date('trip_date')->nullable()->after('destination');
        });
    }

    public function down()
    {
        Schema::table('routes', function (Blueprint $table) {
            // $table->dropColumn('trip_date');
        });
    }
};