<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusLocationsTable extends Migration
{
    /**
     * Run the migrations to create the bus_locations table for storing location history.
     */
    public function up(): void
    {
        if (!Schema::hasTable('bus_locations')) {
            Schema::create('bus_locations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('bus_id');
                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);
                $table->decimal('speed', 8, 2)->nullable()->comment('Speed in km/h');
                $table->decimal('heading', 5, 2)->nullable()->comment('Direction in degrees');
                $table->timestamp('recorded_at');
                $table->string('address')->nullable();
                $table->timestamps();

                $table->foreign('bus_id')
                    ->references('id')
                    ->on('buses')
                    ->onDelete('cascade');                $table->index(['bus_id', 'recorded_at']);
                // MySQL doesn't support multi-column spatial indexes, so let's create separate indexes instead
                $table->index('latitude');
                $table->index('longitude');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bus_locations');
    }
}
