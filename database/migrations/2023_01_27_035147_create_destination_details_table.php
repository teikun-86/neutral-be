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
        Schema::create('destination_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destination_id');
            $table->longText('highlight');
            $table->longText('highlight_en')->nullable();
            $table->longText('highlight_jp')->nullable();
            $table->longText('description');
            $table->longText('description_en')->nullable();
            $table->longText('description_jp')->nullable();
            $table->longText('additional_info');
            $table->longText('additional_info_en')->nullable();
            $table->longText('additional_info_jp')->nullable();
            $table->longText('ticket_redemption');
            $table->longText('ticket_redemption_en')->nullable();
            $table->longText('ticket_redemption_jp')->nullable();
            $table->longText('terms_and_conditions');
            $table->longText('terms_and_conditions_en')->nullable();
            $table->longText('terms_and_conditions_jp')->nullable();
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
        Schema::dropIfExists('destination_details');
    }
};
