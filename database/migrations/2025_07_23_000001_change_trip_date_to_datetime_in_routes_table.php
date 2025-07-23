<?php
// Migration to change trip_date from date to datetime in routes table
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->dateTime('trip_date')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->date('trip_date')->nullable()->change();
        });
    }
};
