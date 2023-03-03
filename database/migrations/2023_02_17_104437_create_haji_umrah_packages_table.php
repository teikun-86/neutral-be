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
        Schema::create('haji_umrah_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_id')->constrained('haji_umrah_flights', 'id');
            $table->foreignId('hotel_id')->constrained('haji_umrah_hotels', 'id');
            $table->bigInteger('packages_available');
            $table->bigInteger('seats_per_package');
            $table->bigInteger('hotels_per_package');
            $table->bigInteger('price_per_package');
            $table->string('program_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('haji_umrah_packages');
    }
};
