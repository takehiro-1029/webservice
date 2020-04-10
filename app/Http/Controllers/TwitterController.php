<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Socialite;
use Abraham\TwitterOAuth\TwitterOAuth;
use GuzzleHttp\Client;
use App\TwitterUser;
use App\UserFollowList;
use App\User;
use App\CryptoComment;

class TwitterController extends Controller
{
    public function twitter()
    {
        $user_token = Auth::user()->where('id',Auth::id())->first();

        if (!empty($user_token->oauth_token)){
            return redirect('/show');
        }else{
            $twitter = new TwitterOAuth(
                config('services.twitter.client_id'),
                config('services.twitter.client_secret')
            );
            # 認証用のrequest_tokenを取得
            $token = $twitter->oauth('oauth/request_token');

            # 認証画面で認証を行うためSessionに入れる
            session(array(
                'oauth_token' => $token['oauth_token'],
                'oauth_token_secret' => $token['oauth_token_secret'],
            ));

            # 認証画面へ移動させる
            ## 毎回認証をさせたい場合： 'oauth/authorize'
            ## 再認証が不要な場合： 'oauth/authenticate'
            $url = $twitter->url('oauth/authenticate', array(
                'oauth_token' => $token['oauth_token']
            ));

            return redirect($url);
        };
    }

     public function twitterCallback(Request $request)
    {
        $oauth_token = session('oauth_token');
        $oauth_token_secret = session('oauth_token_secret');

        # request_tokenが不正な値だった場合エラー
        if ($request->has('oauth_token') && $oauth_token !== $request->oauth_token) {
            return Redirect::to('/mypage');
        }

        # request_tokenからaccess_tokenを取得
        $twitter = new TwitterOAuth(
            $oauth_token,
            $oauth_token_secret
        );
        $token = $twitter->oauth('oauth/access_token', array(
            'oauth_verifier' => $request->oauth_verifier,
            'oauth_token' => $request->oauth_token,
        ));

        session(array(
            'oauth_token' => $token['oauth_token'],
            'oauth_token_secret' => $token['oauth_token_secret'],
        ));

        $oauth_token = session('oauth_token');
        $oauth_token_secret = session('oauth_token_secret');

        Auth::user()->where('id',Auth::id())->update(['oauth_token' => $oauth_token,'oauth_token_secret' => $oauth_token_secret]);

        return redirect('/show');
     }

    public function FollowedShow()
    {
        $api_limit_time = Auth::user()->where('id',Auth::id())->select('api_limit_time')->first();
        $now = Carbon::now();

        if ($now < $api_limit_time->api_limit_time){
            return redirect('/mypage')->with('flash_message', 'API制限中のためしばらくお待ちください。');
        };

        $user_token = Auth::user()->where('id',Auth::id())->first();
        $oauth_token = $user_token->oauth_token;
        $oauth_token_secret = $user_token->oauth_token_secret;

        # access_tokenを用いればユーザー情報へアクセスできるため、それを用いてTwitterOAuthをinstance化
        $twitter_login_user = new TwitterOAuth(
            config('services.twitter.client_id'),
            config('services.twitter.client_secret'),
            $oauth_token,
            $oauth_token_secret
        );

//      ユーザーのフォローリストから未フォローのツイッターアカウント15件取得
        $user_follow_list = new UserFollowList;
        $twitter_user = new TwitterUser;

        $user_nofollowing_account = $user_follow_list->join('twitter_users', 'twitter_users.id', '=', 'users_follow_list.twitter_id')->where('user_id',Auth::id())->where('user_name','!=', 1)->where(function($q){
            $q->where('follow_details','none')
                ->orWhere('follow_details','followed_by');
        })->select('twitter_id')->take(15)->get()->toArray();


//      15件ある場合はアカウント情報を取得してビューに渡す
        if(count($user_nofollowing_account) === 15){
            for ($i = 0; $i < 15; $i++) {
                $user_nofollowing_account[$i] = $twitter_user->where('id',$user_nofollowing_account[$i])->first()->toArray();
            };
//      15件未満の場合はフォロー有無をチェックして15件になるようにする
        }else{
    //      ユーザーが最後にフォロー検索したtwitter_usersテーブルのidをusersテーブルのfollowsearch_numberから取得
            $last_serach_num = Auth::user()->where('id',Auth::id())->value('followsearch_number');
    //      twitter_usersテーブルからテーブルidを取得(最後に検索したidより大きいものを50件取得)
            $twitter_account_id = $twitter_user->orderBy('id', 'asc')->where('id','>',$last_serach_num)->select('account_id')->take(50)->get()->toArray();

    //      連想配列から配列へ変換
            $twitter_account_id = array_column($twitter_account_id, 'account_id');
    //      配列を文字列に変更して,を付ける
            $twitter_account_id = implode(',', $twitter_account_id);
    //      それぞれのユーザーのフォロー有無を確認して$followに格納
            $follow = $twitter_login_user->get('friendships/lookup', array(
                'user_id' => $twitter_account_id,
            ));

            // dd($follow);

            if (!is_array($follow)){
                return redirect('/mypage')->with('flash_message', 'API制限中のためしばらくお待ちください。');
            };

    //      フォロー有無を確認できたユーザーの数（鍵アカ等はデータ取得できないため数を再確認）
            $twitter_user_Num= count($follow);

    //      users_follow_listテーブルにデータを格納するため配列を整える
            $now = Carbon::now();
            for ($i = 0; $i < $twitter_user_Num; $i++) {
                $followuserdata[$i]['twitter_id'] = $twitter_user->where('account_id',$follow[$i]->id)->value('id');
                $followuserdata[$i]['user_id'] = Auth::id();
                $followuserdata[$i]['follow_details'] = $follow[$i]->connections[0];
                $followuserdata[$i]['created_at'] = $now;
                $followuserdata[$i]['updated_at'] = $now;
            };
    //      users_follow_listテーブルに$followuserdataを格納
            $user_follow_list = new UserFollowList;
            $user_follow_list->insert($followuserdata);

    //      次回検索のため最後にフォロー有無を確認したidをusersテーブルに入れておく
            Auth::user()->where('id',Auth::id())->update(['followsearch_number' => $followuserdata[$twitter_user_Num-1]['twitter_id']]);

    //      再度、ユーザーのフォローリストから未フォローのツイッターアカウント15件取得
            $user_nofollowing_account = $user_follow_list->join('twitter_users', 'twitter_users.id', '=', 'users_follow_list.twitter_id')->where('user_id',Auth::id())->where('user_name','!=', 1)->where(function($q){
            $q->where('follow_details','none')
                ->orWhere('follow_details','followed_by');
            })->select('twitter_id')->take(15)->get()->toArray();

            $user_nofollowing_account_num = count($user_nofollowing_account);

            for ($i = 0; $i < $user_nofollowing_account_num; $i++) {
                $user_nofollowing_account[$i] = $twitter_user->where('id',$user_nofollowing_account[$i])->first()->toArray();
            };
        };

        return view('twitter', ['user_nofollowing_account' => $user_nofollowing_account]);

    }

//  仮想通貨に関連するユーザデータ情報を取得する処理(1度に1000件取得してDBに格納する:日によって取得idが変わるか確認必要）
    public function getTimeline()
    {
        set_time_limit(180);
//      twitterにアクセスする
        $twitter = new TwitterOAuth(
            config('services.twitter.client_id'),
            config('services.twitter.client_secret'),
            config('services.twitter.access_token'),
            config('services.twitter.access_token_secret'),
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
                array_push($twitter_id,$user_data[$i][$j]->id);
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
        var_dump($twitter_id);
        // dd ($twitter_id);
//      twitter_userテーブルにaccount_id,create_at,update_atを追加
        $twitter_user = new TwitterUser;
        $twitter_user->insert($twitter_id);
    }

//  twitter_idから1つずつ検索して最新ツイートを含むユーザーデータを取得(cronで実施して1日に1回更新)
    public function gettwitterComment()
    {
        set_time_limit(0);
//      twitterにアクセスする
        $twitter = new TwitterOAuth(
            config('services.twitter.client_id'),
            config('services.twitter.client_secret'),
            config('services.twitter.access_token'),
            config('services.twitter.access_token_secret'),
        );

//      twitter_usersテーブルからtwitterのアカウントidを取得
        $twitter_user = new TwitterUser;
        $twitter_account_id = $twitter_user->orderBy('id', 'asc')->where('day_update_flg', 'false')->select('account_id')->first();

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
                $user_account_data['account_id'] = $timeline[0]->user->id;
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
                $twitter_user->where('account_id',$twitter_account_id->account_id)->update(['day_update_flg' => '1'],['updated_at' => $now]);
                print_r($twitter_account_id->account_id);
            };

            sleep(10);
            $twitter_user = new TwitterUser;
            $twitter_account_id = $twitter_user->orderBy('id', 'asc')->where('day_update_flg', 'false')->select('account_id')->first();

            if(empty($twitter_account_id)){
                $twitter_user = new TwitterUser;
                $twitter_user->where('day_update_flg','1')->update(['day_update_flg' => '0']);
                break;
            };
             print_r('終わり');
        };

        print_r('finish');
    }


//  各通貨のツイート数を取得する処理(cronで30分毎に実行させDBに格納していく関数)
    public function getCryptoComment(){
//      twitterにアクセスする
        $twitter = new TwitterOAuth(
            config('services.twitter.client_id'),
            config('services.twitter.client_secret'),
            config('services.twitter.access_token'),
            config('services.twitter.access_token_secret'),
        );
        // dd($twitter);
        set_time_limit(0);
        $num = 0;
        while($num < 100) {
//      ツイート取得（現在時間から過去30分間のツイート数:関係のないツイートを除くため今回は#を付ける）
        $keywords = ["#BTC", "#ETH", "#ETC","#LSK","#FCT","#XRP","#XEM","#LTC","#BCH","#MONA","#XLM","#QTUM"];
        $sincetime = date('Y-m-d_H:i:s', strtotime("now -10 min"));
        $untiltime = date('Y-m-d_H:i:s');

        $starttime = Carbon::now()->subMinutes(10)->toDateTimeString();
        $endtime = Carbon::now()->toDateTimeString();

//      ツイートを取得する通貨の数
        $Crypto_Num= count($keywords);

//      各通貨ごとのコメント数を格納する配列
        $comment_Num = [];

        for ($i = 0; $i <= $Crypto_Num-1; $i++) {
            $tweetTimeline = $twitter->get('search/tweets', array(
                'q' =>  "{$keywords[$i]},since:{$sincetime}_JST until:{$untiltime}_JST",
                'count'  =>  100,
                ));
                // dd($tweetTimeline);
            $comment_Num[$i] = count($tweetTimeline->statuses);
        };

        $user_account_data['BTC'] = $comment_Num[0];
        $user_account_data['ETH'] = $comment_Num[1];
        $user_account_data['ETC'] = $comment_Num[2];
        $user_account_data['LSK'] = $comment_Num[3];
        $user_account_data['FCT'] = $comment_Num[4];
        $user_account_data['XRP'] = $comment_Num[5];
        $user_account_data['XEM'] = $comment_Num[6];
        $user_account_data['LTC'] = $comment_Num[7];
        $user_account_data['BCH'] = $comment_Num[8];
        $user_account_data['MONA'] = $comment_Num[9];
        $user_account_data['XLM'] = $comment_Num[10];
        $user_account_data['QTUM'] = $comment_Num[11];
        $user_account_data['search_starttime'] = $starttime;
        $user_account_data['search_endtime'] = $endtime;

        $crypto_comment = new CryptoComment;
        $crypto_comment->create($user_account_data);

        sleep (600);

        $num = $num+1;
        echo $num;
        }
    }


     public function CryptoRank()
     {
        return view('cryptorank');

     }


//  グーグルニュース取得(DBにデータを入れる必要なし:マイページ)
    function getNews()
    {
        #検索ワード
        $keywords="仮想通貨";
        #検索API
        $API_BASE_URL = 'https://news.google.com/rss/search?hl=ja&hl=ja&gl=JP&ceid=JP:ja&q=';
        $query = urlencode(mb_convert_encoding($keywords,"UTF-8", "auto"));
        $url =  $API_BASE_URL.$query;
        #通信方式GET or POST
        $method = 'GET';

        $client = new Client();
        $response = $client->request($method, $url);
        $googlenews = simplexml_load_string($response->getBody()->getContents());

        #XML形式を配列に変換
        $googlenews = json_decode(json_encode($googlenews), true);
//        echo json_last_error_msg();

        return view('mypage', ['googlenews' => $googlenews]);
    }

    //
    //  public function Home(){
    //  return view('home');
    // }

    // トップ画面
    public function top()
    {
        return view('top');
    }


}
