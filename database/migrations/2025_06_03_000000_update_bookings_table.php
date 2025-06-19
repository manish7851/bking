<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'bus_name')) {
                $table->string('bus_name')->nullable()->after('bus_id');
            }
            if (!Schema::hasColumn('bookings', 'bus_number')) {
                $table->string('bus_number')->nullable()->after('bus_name');
            }
            if (Schema::hasColumn('bookings', 'price')) {
                $table->decimal('price', 8, 2)->default(0)->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('bus_name');
            $table->dropColumn('bus_number');
            $table->decimal('price', 8, 2)->change();
        });
    }
};
