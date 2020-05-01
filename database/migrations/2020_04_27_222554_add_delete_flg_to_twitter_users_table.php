<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeleteFlgToTwitterUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('twitter_users', function (Blueprint $table) {
            $table->boolean('delete_flg')->default(false); //カラムの追加
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('twitter_users', function (Blueprint $table) {
            $table->dropColumn('delete_flg');  //カラムの削除
        });
    }
}
