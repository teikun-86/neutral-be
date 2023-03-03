<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('haji_umrah_hotel_reservations', function (Blueprint $table) {
            $table->foreignId('package_id')->nullable()->after('hotel_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('haji_umrah_hotel_reservations', function (Blueprint $table) {
            $table->dropColumnIfExists('package_id');
        });
    }
};
