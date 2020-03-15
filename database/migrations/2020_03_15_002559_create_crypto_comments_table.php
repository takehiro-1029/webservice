<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCryptoCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crypto_comments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('BTC')->default(0);
            $table->unsignedInteger('ETH')->default(0);
            $table->unsignedInteger('ETC')->default(0);
            $table->unsignedInteger('LSK')->default(0);
            $table->unsignedInteger('FCT')->default(0);
            $table->unsignedInteger('XRP')->default(0);
            $table->unsignedInteger('XEM')->default(0);
            $table->unsignedInteger('LTC')->default(0);
            $table->unsignedInteger('BCH')->default(0);
            $table->unsignedInteger('MONA')->default(0);
            $table->unsignedInteger('XLM')->default(0);
            $table->unsignedInteger('QTUM')->default(0);
            $table->datetime('search_starttime');
            $table->datetime('search_endtime');
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
        Schema::dropIfExists('crypto_comments');
    }
}
