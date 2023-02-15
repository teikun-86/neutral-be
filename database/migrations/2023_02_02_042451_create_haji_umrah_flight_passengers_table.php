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
        Schema::create('haji_umrah_flight_passengers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_manifest_id');
            $table->string('name');
            $table->string('passport_number')->nullable();
            $table->string('visa_number')->nullable();
            $table->string('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable(); 
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
        Schema::dropIfExists('haji_umrah_flight_passengers');
    }
};
