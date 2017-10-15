<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSslcommerzIpnTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {             
        Schema::create('sslcommerz_ipn', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('hit_receive_time');
            $table->string('hash_verify');
            $table->string('status');
            $table->text('status_message');
            $table->string('trx_status');
            $table->integer('validation_call_status');
            $table->dateTime('tran_date');
            $table->string('tran_id');
            $table->string('val_id');
            $table->double('amount', 15, 8);
            $table->double('amount_with_bank_fee', 15, 8);
            $table->string('currency');
            $table->double('store_amount', 15, 8);
            $table->string('bank_tran_id');
            $table->string('card_type');
            $table->string('card_no');
            $table->string('card_issuer');
            $table->string('card_brand');
            $table->string('card_issuer_country');
            $table->string('card_issuer_country_code');
            $table->string('store_id');

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
        Schema::dropIfExists('sslcommerz_ipn');
    }
}