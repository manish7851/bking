<?php
// Migration to add trip_date to routes table
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->string('source_latitude')->nullable()->after('destination');
            $table->string('source_longitude')->nullable()->after('destination');
            $table->string('destination_latitude')->nullable()->after('destination');
            $table->string('destination_longitude')->nullable()->after('destination');
        });
    }

    public function down()
    {
        Schema::table('routes', function (Blueprint $table) {
            $table->dropColumn('source_latitude');
            $table->dropColumn('source_longitude');
            $table->dropColumn('destination_latitude');
            $table->dropColumn('destination_longitude');
        });
    }
};
