<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeofencesTable extends Migration
{
    /**
     * Run the migrations to create the geofences table.
     */
    public function up(): void
    {
        if (!Schema::hasTable('geofences')) {
            Schema::create('geofences', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->decimal('center_lat', 10, 7);
                $table->decimal('center_lng', 10, 7);
                $table->float('radius')->comment('Radius in meters');
                $table->string('type')->default('poi')->comment('poi, depot, stop, etc.');
                $table->string('description')->nullable();
                $table->string('color', 20)->default('red');
                $table->timestamps();
                
                // MySQL doesn't support multi-column spatial indexes
                $table->index('center_lat');
                $table->index('center_lng');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geofences');
    }
}
