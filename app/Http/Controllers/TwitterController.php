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
//    ツイッターへアクセスして認証するための処理
    public function twitter()
    {
//        ログイン中のユーザーが認証処理済みの場合、DBに値があるので確認、値がなければ認証処理実施
        $user_token = Auth::user()->where('id',Auth::id())->first();

        if (!empty($user_token->oauth_token)){
            return redirect('/show');
        }else{
            $twitter = new TwitterOAuth(
                config('services.twitter.client_id'),
                config('services.twitter.client_secret')
            );
//            認証用のrequest_tokenを取得
            $token = $twitter->oauth('oauth/request_token');

//            認証画面で認証を行うためSessionに入れる
            session(array(
                'oauth_token' => $token['oauth_token'],
                'oauth_token_secret' => $token['oauth_token_secret'],
            ));

//            認証画面へ移動させる
//            毎回認証をさせたい場合： 'oauth/authorize'
//            再認証が不要な場合： 'oauth/authenticate'
            $url = $twitter->url('oauth/authenticate', array(
                'oauth_token' => $token['oauth_token']
            ));

            return redirect($url);
        };
    }

//    ツイッターに通信し返ってきたトークンをDBに保存する処理
     public function twitterCallback(Request $request)
    {
        $oauth_token = session('oauth_token');
        $oauth_token_secret = session('oauth_token_secret');

//        request_tokenが不正な値だった場合エラー
        if ($request->has('oauth_token') && $oauth_token !== $request->oauth_token) {
            return Redirect::to('/mypage');
        }

//        request_tokenからaccess_tokenを取得
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

//        DBにトークンを保存しておく
        Auth::user()->where('id',Auth::id())->update(['oauth_token' => $oauth_token,'oauth_token_secret' => $oauth_token_secret]);

        return redirect('/show');
     }

//    ツイッターデータをDBから取得して、フォローする処理
    public function FollowedShow()
    {
//        ユーザーがAPI制限ではないことをDBの値から判断する
        $api_limit_time = Auth::user()->where('id',Auth::id())->select('api_limit_time')->first();
        $now = Carbon::now();
        
//        api制限中の場合はmypage画面に返す
        if ($now < $api_limit_time->api_limit_time){
            return redirect('/mypage')->with('flash_message', 'API制限中のためしばらくお待ちください。');
        };
        
//        DBからTwitterユーザーデータをすべて取得（鍵垢などでデータが得られていないものは外す）
        $twitter_user = new TwitterUser;

        $user_nofollowing_account = $twitter_user->orderBy('id', 'asc')->where('user_name','!=', 1)->paginate(15);
        
//        自動フォロー機能を有効にしているかDBから確認
        $autofollow_selected = Auth::user()->where('id',Auth::id())->select('autofollow_flg')->first()->autofollow_flg;
        
//        DBからユーザーがフォローした数を取得する
        $follow_num = UserFollowList::where('user_id',Auth::id())->where('follow_details','=', 'following')->count();
        
        return view('twitter', ['user_nofollowing_account' => $user_nofollowing_account,'autofollow_selected' => $autofollow_selected,'follow_num' => $follow_num]);

    }

//     トレンド画面へ遷移
     public function CryptoRank()
     {
        return view('cryptorank');

     }


//    グーグルニュース取得(DBにデータを入れる必要なし:マイページ)
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
    
//    トップ画面
    public function top()
    {
        return view('top');
    }
    
    
    
    
    
    
    
    
    public function test()
    {
        
        set_time_limit(0);
        
//        userDBからautofollow機能をonにしているユーザーを取り出す
        $auto_follow_user = User::orderBy('id', 'asc')->where('autofollow_flg', 1)->get()->Toarray();
        
//        該当ユーザーがいなければ処理は終了
        if(empty($auto_follow_user)){
            var_dump('処理終了');
            return;  
        };
        
        dump($auto_follow_user);
        
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
            
            dump($follow_user);
            
//            取り出したユーザーをフォローするためにループを回す
            for ($j = 0; $j < count($follow_user); $j++) {
                
                
//                検索回数がAPI制限にかかってないかの確認
                $api_limit = $twitter_user->get('application/rate_limit_status', array(
                        'resources' => 'friendships',
                ));

//                データが取得できない場合は処理をスキップして対象ユーザーのautofollowフラグをfalseにする
                if (property_exists($api_limit,'errors')){
                    
                    var_dump('errorだお');
                    User::where('id',$auto_follow_user[$i]['id'])->update(['autofollow_flg' => 0]);
                    break;
                };

                $api_limit = json_decode(json_encode($api_limit),true);
                
                $api_limit_num = $api_limit['resources']['friendships']['/friendships/lookup']['remaining'];
                
                dump($api_limit_num);

                // API制限にかかっている場合、処理をスキップする
                if($api_limit_num === 0){
                    var_dump('0になったお');
                    break;
                };
                
                $follow_name = $follow_user[$j]['screen_name'];
                
                dump($follow_name);
                
//                フォロー前に二重フォローを防ぐためにユーザーとの関係を再確認
                $follow = $twitter_user->get('friendships/lookup', array(
                  'screen_name' => $follow_name,
                ));
                
                dump($follow[0]->connections[0]);
                
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
