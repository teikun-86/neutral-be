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
        Schema::create('destination_catalogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destination_id');
            $table->string('name');
            $table->text('description');
            $table->text('description_en');
            $table->text('description_jp');
            $table->bigInteger('price');
            $table->decimal('discount');
            $table->string('discount_type')->default('percent');
            $table->string('valid_at')->default('select_date');
            $table->json('ticket_includes')->nullable();
            $table->json('ticket_includes_en')->nullable();
            $table->json('ticket_includes_jp')->nullable();
            $table->boolean('must_reserve');
            $table->boolean('is_refundable');
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
        Schema::dropIfExists('destination_catalogs');
    }
};
