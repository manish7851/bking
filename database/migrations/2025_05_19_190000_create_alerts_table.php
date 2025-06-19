<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bus_id');
            $table->string('type'); // 'overspeed', 'deviation', 'idle', etc.
            $table->string('message');
            $table->json('data')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('bus_id')->references('id')->on('buses')->onDelete('cascade');
        });
    }
    public function down()
    {
        Schema::dropIfExists('alerts');
    }
};
