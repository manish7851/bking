<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateTotalSeatsForBuses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // DB::table('buses')
        //     ->where('bus_number', '912')
        //     ->update(['total_seats' => 40]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // DB::table('buses')
        //     ->where('bus_number', '912')
        //     ->update(['total_seats' => 0]);
    }
}
