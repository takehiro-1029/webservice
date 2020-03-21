<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddTokenToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
             $table->string('oauth_token')->default("");  //カラム追加
             $table->string('oauth_token_secret')->default("");  //カラム追加
             $table->datetime('api_limit_time')->default(DB::raw('CURRENT_TIMESTAMP'));;  //カラム追加
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
             $table->dropColumn('oauth_token');  //カラムの削除
             $table->dropColumn('oauth_token_secret');  //カラムの削除
             $table->dropColumn('api_limit_time');  //カラムの削除
        });
    }
}
