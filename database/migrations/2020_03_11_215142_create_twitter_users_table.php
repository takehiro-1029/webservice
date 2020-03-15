<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTwitterUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twitter_users', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('account_id')->unique();
            $table->string('user_name')->default("");
            $table->string('screen_name')->default("");
            $table->string('profile_image_url')->default("");
            $table->string('description')->default("");
            $table->unsignedInteger('follows_count')->default(0);
            $table->unsignedInteger('friends_count')->default(0);
            $table->string('recent_tweet')->default(0);
            $table->boolean('day_update_flg')->default(false);
            $table->boolean('delete_flg')->default(false);
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
        Schema::dropIfExists('twitter_users');
    }
}
