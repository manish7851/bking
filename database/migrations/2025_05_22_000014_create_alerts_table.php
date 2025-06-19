<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlertsTable extends Migration
{
    /**
     * Run the migrations to create the alerts table.
     */
    public function up(): void
    {
        if (!Schema::hasTable('alerts')) {
            Schema::create('alerts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('bus_id');
                $table->string('type', 50)->comment('overspeed, deviation, idle, geofence, battery, etc.');
                $table->text('message');
                $table->json('data')->nullable();
                $table->string('severity', 20)->default('info')->comment('info, warning, critical');
                $table->boolean('is_read')->default(false);
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->timestamps();

                $table->foreign('bus_id')
                    ->references('id')
                    ->on('buses')
                    ->onDelete('cascade');
                    
                $table->index(['bus_id', 'created_at']);
                $table->index(['type', 'is_read']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
}
