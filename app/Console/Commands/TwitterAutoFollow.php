<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Abraham\TwitterOAuth\TwitterOAuth;
use Carbon\Carbon;
use App\TwitterUser;
use App\User;
use App\UserFollowList;

class TwitterAutoFollow extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:twitterautofollow';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Twitter auto follow command';

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
        
//        userDBからautofollow機能をonにしているユーザーを取り出す
        $auto_follow_user = User::orderBy('id', 'asc')->where('autofollow_flg', 1)->get()->Toarray();
        
//        該当ユーザーがいなければ処理は終了
        if(empty($auto_follow_user)){
            var_dump('処理終了');
            return;  
        };
        
//        対象user分ループを回す
        for ($i = 0; $i < count($auto_follow_user); $i++) {
            
            // 対象ユーザーがAPI制限中でないことをDBのapi_limit_timeから確認
            $now = Carbon::now();
            if ($now < $auto_follow_user[$i]['api_limit_time']){
                continue;
            };
            
//            DBからTwitterのアカウントトークンを取得
            $oauth_token = $auto_follow_user[$i]['oauth_token'];
            $oauth_token_secret = $auto_follow_user[$i]['oauth_token_secret'];

//            access_tokenを用いてTwitterOAuthをinstance化
            $twitter_user = new TwitterOAuth(
                config('services.twitter.client_id'),
                config('services.twitter.client_secret'),
                $oauth_token,
                $oauth_token_secret
            );
            
//            DBからフォローしたいユーザーを10件取り出す
            $follow_user = TwitterUser::orderBy('id', 'asc')->where('user_name','!=', 1)->where('id','>',$auto_follow_user[$i]['followsearch_number'])->take(10)->get()->Toarray();
            
//            フォローしたいユーザーが0の場合は処理をスキップして対象ユーザーのautofollowフラグをfalseにする
            if(count($follow_user) === 0){
                User::where('id',$auto_follow_user[$i]['id'])->update(['autofollow_flg' => 0]);
                continue;
            };
            
//            取り出したユーザーをフォローするためにループを回す
            for ($j = 0; $j < count($follow_user); $j++) {
                
                
//                検索回数がAPI制限にかかってないかの確認
                $api_limit = $twitter_user->get('application/rate_limit_status', array(
                        'resources' => 'friendships',
                ));

//                データが取得できない場合は処理をスキップして対象ユーザーのautofollowフラグをfalseにする
                if (property_exists($api_limit,'errors')){
                    
                    User::where('id',$auto_follow_user[$i]['id'])->update(['autofollow_flg' => 0]);
                    break;
                };

                $api_limit = json_decode(json_encode($api_limit),true);
                
                $api_limit_num = $api_limit['resources']['friendships']['/friendships/lookup']['remaining'];
                
                // API制限にかかっている場合、処理をスキップする
                if($api_limit_num === 0){
                    break;
                };
                
                $follow_name = $follow_user[$j]['screen_name'];
                
//                フォロー前に二重フォローを防ぐためにユーザーとの関係を再確認
                $follow = $twitter_user->get('friendships/lookup', array(
                  'screen_name' => $follow_name,
                ));
                
                
//                フォローするユーザとの関係がnoneもしくはfollowed_byの場合はフォローを行う
                if($follow[0]->connections[0] === 'none' || $follow[0]->connections[0] === 'followed_by'){
                    
//                    フォローする
                    $friendship_create = $twitter_user->post('friendships/create',array(
                           'screen_name' => $follow_name,
                    ));
                    
//                    フォロー制限が掛かっている場合は1hフォローできないようにする
                    if(!property_exists($friendship_create, 'id')){
                        
                        $api_limit_time = $now->addminute(60);
                        User::where('id',$auto_follow_user[$i]['id'])->update(['api_limit_time' => $api_limit_time]);
                        break;                  
                    };       
                };
                
//                DBに重複してデータを入れないように判定
                $db_exists = UserFollowList::where('user_id',$auto_follow_user[$i]['id'])->where('twitter_id',$follow_user[$j]['id'])->first();
                
                if($db_exists === null){   
                    $twitter_id = array('user_id' => $auto_follow_user[$i]['id'],'twitter_id' => $follow_user[$j]['id'],'follow_details' => 'following','created_at' =>$now,'updated_at' =>$now);
                    UserFollowList::create($twitter_id);
                };
                
                User::where('id',$auto_follow_user[$i]['id'])->update(['followsearch_number' => $follow_user[$j]['id']]);
                
            };
        };
    }
}
