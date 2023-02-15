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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_method_id');
            $table->string('payable_type');
            $table->string('payable_id');
            $table->string('payment_code')->unique();
            $table->bigInteger('amount');
            $table->string('status')->default('unpaid');
            $table->string('proof_of_payment')->nullable();
            $table->timestamps();
            $table->index(['payable_type', 'payable_id'], 'payable_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
