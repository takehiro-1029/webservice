<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Abraham\TwitterOAuth\TwitterOAuth;
use Carbon\Carbon;
use App\TwitterUser;

class GetTwitterAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:getnewaccount';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'New Twitter account get';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
      set_time_limit(180);
//      twitterにアクセスする
      $twitter = new TwitterOAuth(
          config('services.twitter.client_id'),
          config('services.twitter.client_secret'),
          config('services.twitter.access_token'),
          config('services.twitter.access_token_secret')
      );

//      twitterのuserデータを格納する配列
      $user_data=[];
//      取得するユーザーデータの数($getPageNum×20)
      $getPageNum = 50;
//      ループでTwitterAPIからユーザーデータを20件ずつ取得して$user_dataに格納
      for ($i = 1; $i <= $getPageNum; $i++) {
          $searchUser = $twitter->get('users/search',array(
              'q' => '仮想通貨',
              'page' => $i,
              'count' => 20,
          ));
          $user_data[$i] = $searchUser;
      };

      // var_dump($user_data);

//      $user_dataからtwitter_idを取り出して格納する配列
      $twitter_id =[];
//      ループで$user_dataからtwitter_idを取り出して$twitter_idに格納
      for ($i = 1; $i <= $getPageNum; $i++) {
          for ($j = 0; $j <= 19; $j++) {
            if(isset($user_data[$i][$j])){
              array_push($twitter_id,$user_data[$i][$j]->id_str);
            }
          };
      };

      $getUsersNum = count($twitter_id);
      for ($i = 0; $i < $getUsersNum; $i++) {
          $db_exist_judge = json_decode(TwitterUser::where('account_id', '=', $twitter_id[$i])->get());
          if(!empty($db_exist_judge)){
            unset($twitter_id[$i]);
          };
      };
      $twitter_id = array_values($twitter_id);

      $getUsersNum = count($twitter_id);

//      データ追加用にキーを指定
      $now = Carbon::now();
      for ($i = 0; $i < $getUsersNum; $i++) {
            $twitter_id[$i] = array('account_id' =>$twitter_id[$i],'created_at' =>$now,'updated_at' =>$now);
      };
//      var_dump($twitter_id);
      // dd ($twitter_id);
//      twitter_userテーブルにaccount_id,create_at,update_atを追加
      $twitter_user = new TwitterUser;
      $twitter_user->insert($twitter_id);
    }
}
