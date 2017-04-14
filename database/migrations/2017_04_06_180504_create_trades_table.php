<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->datetime('date')->nullable();
            $table->string('platform_id');
            $table->string('trade_id');
            $table->string('type');
            $table->string('source_currency');
            $table->string('target_currency');
            $table->float('rate', 16, 8);
            $table->float('volume', 16, 8);
            $table->float('fee_fiat', 16, 8);
            $table->float('fee_coin', 16, 8);
            $table->float('purchase_rate_fiat_btc', 16, 8 )->nullable();
            $table->float('revenue_btc', 16, 8 )->nullable();
            $table->float('revenue_fiat', 16, 8 )->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trades');
    }
}
