<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropTrackingRelatedTables extends Migration
{
    /**
     * Run the migrations to remove tracking-related tables.
     */
    public function up(): void
    {
        // Drop tracking-related tables if they exist
        Schema::dropIfExists('geofence_events');
        Schema::dropIfExists('bus_locations');
        Schema::dropIfExists('alerts');
        Schema::dropIfExists('geofences');
    }

    /**
     * Reverse the migrations.
     * Note: This is a simplistic recreation of tables and doesn't restore data.
     */
    public function down(): void
    {
        // Recreate geofences table
        if (!Schema::hasTable('geofences')) {
            Schema::create('geofences', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->decimal('center_lat', 10, 7);
                $table->decimal('center_lng', 10, 7);
                $table->float('radius'); // in meters
                $table->timestamps();
            });
        }

        // Recreate bus_locations table
        if (!Schema::hasTable('bus_locations')) {
            Schema::create('bus_locations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('bus_id');
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->float('speed')->nullable();
                $table->timestamp('recorded_at');
                $table->foreign('bus_id')->references('id')->on('buses')->onDelete('cascade');
                $table->index(['bus_id', 'recorded_at']);
                $table->timestamps();
            });
        }

        // Recreate geofence_events table
        if (!Schema::hasTable('geofence_events')) {
            Schema::create('geofence_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('bus_id');
                $table->unsignedBigInteger('geofence_id');
                $table->enum('event_type', ['enter', 'exit']);
                $table->timestamp('event_time');
                $table->foreign('bus_id')->references('id')->on('buses')->onDelete('cascade');
                $table->foreign('geofence_id')->references('id')->on('geofences')->onDelete('cascade');
                $table->timestamps();
            });
        }

        // Recreate alerts table
        if (!Schema::hasTable('alerts')) {
            Schema::create('alerts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('bus_id');
                $table->string('type');
                $table->text('message');
                $table->text('data')->nullable();
                $table->timestamp('created_at');
                $table->foreign('bus_id')->references('id')->on('buses')->onDelete('cascade');            });
        }
    }
}
