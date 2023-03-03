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
        Schema::create('haji_umrah_hotels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable();
            $table->string('program_type');
            $table->string('location_1');
            $table->string('location_2');
            $table->json('room_detail');
            $table->bigInteger('packages_available');
            $table->bigInteger('price_per_package');
            $table->timestamp('first_check_in_at');
            $table->timestamp('first_check_out_at');
            $table->timestamp('last_check_in_at');
            $table->timestamp('last_check_out_at');
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
        Schema::dropIfExists('haji_umrah_hotels');
    }
};
