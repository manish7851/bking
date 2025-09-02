<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
Schema::table('bookings', function (Blueprint $table) {
    if (!Schema::hasColumn('bookings', 'payment_status')) {
        $table->string('payment_status')->nullable();
    }
    // payment_method is already added by an earlier migration (2025_05_18_183720_add_payment_status_to_bookings_table.php)
    // So, we don't need to add it again here.


});

    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
            if (Schema::hasColumn('bookings', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
        });
    }
};
