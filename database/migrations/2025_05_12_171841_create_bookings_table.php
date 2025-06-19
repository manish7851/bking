<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
  Schema::create('bookings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('customer_id')->constrained()->onDelete('cascade');
    $table->foreignId('bus_id')->constrained()->onDelete('cascade');
   

    $table->foreignId('route_id')->constrained()->onDelete('cascade');
    $table->string('seat');
    $table->string('source');
$table->string('destination');
$table->string('bus_name')->nullable();
$table->string('bus_number')->nullable();
$table->decimal('price', 8, 2)->default(0); // Add price column
    $table->string('status')->default('Booked');
    $table->timestamps();
});

}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
