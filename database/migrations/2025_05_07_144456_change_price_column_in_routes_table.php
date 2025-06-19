<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangePriceColumnInRoutesTable extends Migration
{
    public function up()
    {
        Schema::table('routes', function (Blueprint $table) {
            // Change 'price' to a bigger type (decimal is recommended)
            $table->decimal('price', 10, 2)->change();
        });
    }

    public function down()
    {
        Schema::table('routes', function (Blueprint $table) {
            // Revert back to original type if needed
            $table->integer('price')->change();
        });
    }
}

