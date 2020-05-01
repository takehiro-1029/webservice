<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Abraham\TwitterOAuth\TwitterOAuth;
use Carbon\Carbon;
use App\TwitterUser;

class UpdateTwitterUserdata extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:updateaccount';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Twitter account data update';

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
      set_time_limit(0);
//      twitterにアクセスする
      $twitter = new TwitterOAuth(
          config('services.twitter.client_id'),
          config('services.twitter.client_secret'),
          config('services.twitter.access_token'),
          config('services.twitter.access_token_secret')
      );

//        twitter_usersテーブルから未更新の（day_update_flgがfalse）twitterのアカウントidを取得
      $twitter_user = new TwitterUser;
      $twitter_account_id = $twitter_user->orderBy('id', 'asc')->where('day_update_flg', 'false')->where('delete_flg', 'false')->select('account_id')->first();

//        ループで更新対象のユーザーがなくなるまで処理する(10秒ごとに1ユーザー更新していく)    
      while(!empty($twitter_account_id)) {
          $timeline = $twitter->get('statuses/user_timeline', array(
              'user_id' =>  $twitter_account_id->account_id,
              'count' =>  1,
              'trim_user' =>  false,
              'exclude_replies' =>  true,
              'include_rts' =>  false,
          ));
          
          $user_account_data = [];
          $now = Carbon::now();

          if(!empty($timeline) && is_array($timeline)){
              $user_account_data['account_id'] = $timeline[0]->user->id_str;
              $user_account_data['user_name'] = $timeline[0]->user->name;
              $user_account_data['screen_name'] = $timeline[0]->user->screen_name;
              $user_account_data['profile_image_url'] = $timeline[0]->user->profile_image_url;
              $user_account_data['description'] = $timeline[0]->user->description;
              $user_account_data['follows_count'] = $timeline[0]->user->followers_count;
              $user_account_data['friends_count'] = $timeline[0]->user->friends_count;
              $user_account_data['recent_tweet'] = $timeline[0]->text;
              $user_account_data['day_update_flg'] = true;
              $user_account_data['updated_at'] = $now;
              $twitter_user->where(['account_id' => $user_account_data['account_id']])->update($user_account_data);
          }else{
              $twitter_user->where('account_id',$twitter_account_id->account_id)->update(['day_update_flg' => '1'],['delete_flg' => '1'],['updated_at' => $now]);
          };

          sleep(10);
          $twitter_user = new TwitterUser;
          $twitter_account_id = $twitter_user->orderBy('id', 'asc')->where('day_update_flg', 'false')->where('delete_flg', 'false')->select('account_id')->first();

//          すべてのユーザーが更新できたらflgをfalseに戻してbreak処理
          if(empty($twitter_account_id)){
              $twitter_user = new TwitterUser;
              $twitter_user->where('day_update_flg','1')->update(['day_update_flg' => '0']);
              break;
          };
      };
    }
}
