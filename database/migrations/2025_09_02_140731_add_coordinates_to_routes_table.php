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
        // Schema::table('routes', function (Blueprint $table) {
        //     $table->string('source_latitude')->nullable()->after('destination');
        //     $table->string('source_longitude')->nullable()->after('source_latitude');
        //     $table->string('destination_latitude')->nullable()->after('source_longitude');
        //     $table->string('destination_longitude')->nullable()->after('destination_latitude');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('routes', function (Blueprint $table) {
        //     $table->dropColumn('source_latitude');
        //     $table->dropColumn('source_longitude');
        //     $table->dropColumn('destination_latitude');
        //     $table->dropColumn('destination_longitude');
        // });
    }
};
