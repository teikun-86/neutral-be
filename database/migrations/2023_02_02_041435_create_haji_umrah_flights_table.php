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
        Schema::create('haji_umrah_flights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('airline_id');
            $table->foreignId('company_id');
            $table->foreignId('departure_airport_id');
            $table->foreignId('arrival_airport_id');
            $table->foreignId('return_departure_airport_id');
            $table->foreignId('return_arrival_airport_id');
            $table->bigInteger('price');
            $table->integer('seats')->default(0);
            $table->string('flight_number')->nullable();
            $table->timestamp('depart_at');
            $table->timestamp('arrive_at');
            $table->timestamp('return_depart_at');
            $table->timestamp('return_arrive_at');
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
        Schema::dropIfExists('haji_umrah_flights');
    }
};
