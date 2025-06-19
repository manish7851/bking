<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeofenceEventsTable extends Migration
{
    /**
     * Run the migrations to create the geofence_events table.
     */
    public function up(): void
    {
        if (!Schema::hasTable('geofence_events')) {
            Schema::create('geofence_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('bus_id');
                $table->unsignedBigInteger('geofence_id');
                $table->enum('event_type', ['enter', 'exit', 'dwell']);
                $table->timestamp('event_time');
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->decimal('speed', 8, 2)->nullable();
                $table->timestamps();

                $table->foreign('bus_id')
                    ->references('id')
                    ->on('buses')
                    ->onDelete('cascade');
                    
                $table->foreign('geofence_id')
                    ->references('id')
                    ->on('geofences')
                    ->onDelete('cascade');

                $table->index(['bus_id', 'event_time']);
                $table->index(['geofence_id', 'event_time']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geofence_events');
    }
}
