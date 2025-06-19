<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('qr_code_path')->nullable()->after('payment_details');
            $table->string('verification_code', 10)->nullable()->after('qr_code_path');
            $table->timestamp('qr_generated_at')->nullable()->after('verification_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['qr_code_path', 'verification_code', 'qr_generated_at']);
        });
    }
};
