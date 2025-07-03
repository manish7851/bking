<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->boolean('isadmin')->default(false);
            $table->string('email');
            $table->unsignedBigInteger('alert_id');
            $table->boolean('delivered')->default(false);
            $table->timestamps();

            $table->foreign('alert_id')->references('id')->on('alerts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
