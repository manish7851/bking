<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Schema::create('bus_trackings', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('bus_id')->constrained('buses')->onDelete('cascade');
        //     $table->timestamp('started_at')->nullable();
        //     $table->timestamp('ended_at')->nullable();
        //     $table->timestamps();
        // });
    }

    public function down(): void
    {
        // Schema::dropIfExists('bus_trackings');
    }
};
