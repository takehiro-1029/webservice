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

        $user_nofollowing_account = $twitter_user->orderBy('id', 'asc')->where('user_name','!=', 1)->where('delete_flg', 0)->paginate(15);
        
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

        return view('news', ['googlenews' => $googlenews]);
    }
    
//    トップ画面
    public function top()
    {
        return view('top');
    }
}
