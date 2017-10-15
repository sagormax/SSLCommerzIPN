<?php
namespace SSLWIRELESS\SSLCommerzIPN\Migrations;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('site_id');
            $table->string('order_prefix');
            $table->string('voucher_number');
            $table->string('customer_name', 200);
            $table->string('email');
            $table->string('contact');
            $table->text('details');
            $table->string('payment_type');
            $table->string('payment_status');
            $table->double('customer_cost', 15, 8);
            $table->double('discount', 15, 8);
            $table->double('total_cost', 15, 8);
            $table->integer('emi_avail');
            $table->integer('status');
            $table->integer('created_by');
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
        Schema::dropIfExists('orders');
    }
}