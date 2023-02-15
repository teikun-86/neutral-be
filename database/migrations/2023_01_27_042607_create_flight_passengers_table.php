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
        Schema::create('flight_passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_order_id');
            $table->foreignId('country_id')->nullable();
            $table->foreignId('passport_issuer_country_id')->nullable();
            $table->string('title');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('passport_number')->nullable();
            $table->date('birth_date')->nullable();
            $table->date('passport_expiry_date')->nullable();
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
        Schema::dropIfExists('flight_passengers');
    }
};
