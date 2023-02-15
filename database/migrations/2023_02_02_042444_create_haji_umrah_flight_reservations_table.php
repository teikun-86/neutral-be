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
        Schema::create('haji_umrah_flight_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_id');
            $table->foreignId('user_id');
            $table->foreignId('company_id')->nullable();
            $table->bigInteger('price_per_seat');
            $table->bigInteger('total_price');
            $table->integer('seats');
            $table->string('status');
            $table->boolean('pool')->default(false);
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('reserved_at')->nullable();
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
        Schema::dropIfExists('haji_umrah_flight_reservations');
    }
};
