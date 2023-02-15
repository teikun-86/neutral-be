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
        Schema::create('departure_flights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_order_id');
            $table->foreignId('departure_airport_id');
            $table->foreignId('arrival_airport_id');
            $table->foreignId('airline_id');
            $table->string('class');
            $table->string('flight_number');
            $table->string('duration');
            $table->bigInteger('price');
            $table->string('currency_code');
            $table->string('class_code');
            $table->timestamp('departure_at')->nullable()->default(null);
            $table->timestamp('arrival_at')->nullable()->default(null);
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
        Schema::dropIfExists('departure_flights');
    }
};
