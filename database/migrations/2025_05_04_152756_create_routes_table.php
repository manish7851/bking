<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoutesTable extends Migration
{
    public function up()
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->string('destination');
            $table->decimal('price', 8, 2);
            $table->foreignId('bus_id')->nullable()->constrained('buses')->onDelete('set null');
            $table->timestamps();
        });
    }
    

    public function down()
    {
        Schema::dropIfExists('routes');
    }
}
