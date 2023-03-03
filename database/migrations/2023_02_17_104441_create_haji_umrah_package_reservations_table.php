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
        Schema::create('haji_umrah_package_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id');
            $table->foreignId('user_id');
            $table->foreignId('company_id');
            $table->bigInteger('amount');
            $table->bigInteger('price_per_package');
            $table->bigInteger('total_price');
            $table->timestamp('expired_at')->nullable();
            $table->timestamp('reserved_at')->nullable();
            $table->boolean('is_resell')->default(false);
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
        Schema::dropIfExists('haji_umrah_package_reservations');
    }
};
