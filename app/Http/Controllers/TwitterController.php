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
        
//        DBからツイッター接続するためのトークンを取得
        $user_token = Auth::user()->where('id',Auth::id())->first();
        $oauth_token = $user_token->oauth_token;
        $oauth_token_secret = $user_token->oauth_token_secret;

//        access_tokenを用いればユーザー情報へアクセスできるため、それを用いてTwitterOAuthをinstance化
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
    //      twitter_usersテーブルからidを取得(最後に検索したidより大きいものを50件取得)
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
                $followuserdata[$i]['twitter_id'] = $twitter_user->where('account_id',$follow[$i]->id_str)->value('id');
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


}
