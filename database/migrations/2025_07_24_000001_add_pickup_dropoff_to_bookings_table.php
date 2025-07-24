<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('pickup_location')->nullable()->after('status');
            $table->string('pickup_remark')->nullable()->after('pickup_location');
            $table->string('dropoff_location')->nullable()->after('pickup_remark');
            $table->string('dropoff_remark')->nullable()->after('dropoff_location');
        });
    }

    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['pickup_location', 'pickup_remark', 'dropoff_location', 'dropoff_remark']);
        });
    }
};
