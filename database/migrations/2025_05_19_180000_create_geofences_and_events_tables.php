<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('geofences', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('center_lat', 10, 7);
            $table->decimal('center_lng', 10, 7);
            $table->float('radius'); // in meters
            $table->timestamps();
        });

        Schema::create('geofence_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bus_id');
            $table->unsignedBigInteger('geofence_id');
            $table->enum('event_type', ['enter', 'exit']);
            $table->timestamp('event_time');
            $table->foreign('bus_id')->references('id')->on('buses')->onDelete('cascade');
            $table->foreign('geofence_id')->references('id')->on('geofences')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('geofence_events');
        Schema::dropIfExists('geofences');
    }
};
