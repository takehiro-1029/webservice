<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Abraham\TwitterOAuth\TwitterOAuth;
use App\TwitterUser;
use App\UserFollowList;
use App\User;
use GuzzleHttp\Client;
use App\CryptoComment;

class UseAxiosController extends Controller
{
    public function follow(Request $request)
    {
       // フォローしたいユーザーのスクリーンネームを取得
        $name = $request->input('action');

       // ログイン中のユーザーがAPI制限中でないことをDBのapi_limit_timeから確認
        $api_limit_time = Auth::user()->where('id',Auth::id())->select('api_limit_time')->first();
        $now = Carbon::now();

        if ($now < $api_limit_time->api_limit_time){
            return response()->json([
                 'message' => 'API制限中のため15minお待ちください',
             ]);
        };
        
       // DBからTwitterのアカウントトークンを取得
        $user_token = Auth::user()->where('id',Auth::id())->first();
        $oauth_token = $user_token->oauth_token;
        $oauth_token_secret = $user_token->oauth_token_secret;

        # access_tokenを用いてTwitterOAuthをinstance化
        $twitter_login_user = new TwitterOAuth(
            config('services.twitter.client_id'),
            config('services.twitter.client_secret'),
            $oauth_token,
            $oauth_token_secret
        );

        // 検索回数がAPI制限にかかってないかの確認
        $api_limit = $twitter_login_user->get('application/rate_limit_status', array(
                'resources' => 'friendships',
        ));

        // データが取得できない場合はメッセージを返す
        if (property_exists($api_limit,'errors')){
          return response()->json([
            'message' => '登録アカウントではないためフォロー機能は使用できません',
          ]);
        };

        $api_limit = json_decode(json_encode($api_limit),true);
        $api_limit_num = $api_limit['resources']['friendships']['/friendships/lookup']['remaining'];

        // API制限にかかった場合、16min間フォローできないようにする
        if($api_limit_num === 0){
            $api_limit_time = Carbon::now()->addminute(16);
            Auth::user()->where('id',Auth::id())->update(['api_limit_time' => $api_limit_time]);
            return response()->json([
                 'message' => 'API制限中のため15minお待ちください',
             ]);
        };

        // フォロー前に二重フォローを防ぐためにユーザーとの関係を再確認
        $follow = $twitter_login_user->get('friendships/lookup', array(
          'screen_name' => $name,
        ));

        // ユーザーとの関係がnone,followed_byの場合はフォローを行い、それ以外はフォロー済みと判定
        if($follow[0]->connections[0] === 'none' || $follow[0]->connections[0] === 'followed_by'){
            #フォローする
            $searchUser = $twitter_login_user->post('friendships/create',array(
                   'screen_name' => $name,
            ));
            
            // $searchUserに値がない場合はフォロー制限のため60min間フォローできないようにする
            if(!property_exists($searchUser, 'id')){
                $api_limit_time = Carbon::now()->addminute(60);
                Auth::user()->where('id',Auth::id())->update(['api_limit_time' => $api_limit_time]);

                return response()->json([
                 'message' => 'フォロー制限のため1時間待ってください。',
                ]);
            };

            $flash_message = 'フォローしました';

        }else{
            $flash_message = 'フォロー済みのアカウントです';
        };
        
//      DBにフォローユーザー情報を格納
        $twitter_user = new TwitterUser;
        $user_follow_list = new UserFollowList;
        $twitter_table_id = $twitter_user->where('screen_name',$name)->select('id')->first();
        
        $now = Carbon::now();
//      DBに重複してデータを入れないように判定
        $db_exists = $user_follow_list->where('user_id',Auth::id())->where('twitter_id',$twitter_table_id->id)->first();
        
        if($db_exists === null){   
            $twitter_id = array('user_id' => Auth::id(),'twitter_id' => $twitter_table_id->id,'follow_details' => 'following','created_at' =>$now,'updated_at' =>$now);
            $user_follow_list->create($twitter_id);
        };
        
//        DBからユーザーがフォローした数を取得する
        $follow_num = UserFollowList::where('user_id',Auth::id())->where('follow_details','=', 'following')->count();
        
        return response()->json([
            'message' => $flash_message,
            'follow_num' => $follow_num,
        ]);
    }
    
    public function autofollow(Request $request)
    {
//        userDBのautofolow_flgを更新
        $auto_follow_flg = $request->input('action');
        $db_flg = Auth::user()->where('id',Auth::id())->select('autofollow_flg')->first()->autofollow_flg;
        
//        メッセージを返す
        if($auto_follow_flg == $db_flg){
            $flash_message = "現在設定値と同じなので更新できません。";
        }else{
            Auth::user()->where('id',Auth::id())->update(['autofollow_flg' => $auto_follow_flg]);
            if($auto_follow_flg){
                $flash_message = "自動フォロー機能をOnにしました。";
            }else{
                $flash_message = "自動フォロー機能をOffにしました。";
            };
        };
        
        return response()->json(['message' => $flash_message]);
    }

//  コインチェックのAPIからビットコインの過去24時間の最高値と最低値＋各通貨の現在の価格を取得
    function coincheck()
    {
        $client = new Client( [
            'base_uri' => 'https://coincheck.com/api/',
        ] );
        $method='GET';

//      ビットコインの過去24時間の最高値と最低値を取得
        $path='ticker';
        $response = $client->request($method, $path);
        $btc_rate = json_decode($response->getBody()->getContents(), true);

//      各通貨の現在価格を取得
//      取得通貨のurl設定
        $path='rate/';
        $cryptotype = ["btc_jpy", "eth_jpy", "etc_jpy","lsk_jpy","fct_jpy","xrp_jpy","xem_jpy","ltc_jpy","bch_jpy","mona_jpy","xlm_jpy","qtum_jpy"];
//      ループで回すために取得通貨数を取得
        $CryptoNum = count($cryptotype);
//      ループで各通貨の現在価格を取得して$CryptoCurrentValに配列で格納
        for ($i = 0; $i <= $CryptoNum-1; $i++) {
            $response = $client->request($method, $path.$cryptotype[$i]);
            $posts = json_decode($response->getBody()->getContents(), true);
            $current_price[$i] = round($posts['rate'],1);
        };
//        var_dump($current_price);

        return response()->json([
            'btc_rate' => $btc_rate,
            'current_price' => $current_price,
        ]);
    }

//  DBから1週間分のツイッターコメントを取り出す
    public function weekcomment()
    {
        $now = Carbon::now();
        $subweek = Carbon::now()->subDay(7);

        $crypto_comment = new CryptoComment;
        $search_endtime = $crypto_comment->orderBy('id', 'desc')->select('search_endtime')->first();

        $keywords = ["BTC","ETH","ETC","LSK","FCT","XRP","XEM","LTC","BCH","MONA","XLM","QTUM"];
        $Crypto_Num= count($keywords);

        // 現在時間から1週間前までの各通貨のコメント数を合計して取得する
        for ($i = 0; $i <= $Crypto_Num-1; $i++) {
             $comment_sum_week[$keywords[$i]] = (int)$crypto_comment->whereBetween('search_endtime',[$subweek,$now])->sum($keywords[$i]);
        };

        return response()->json([
            'weekcomment' => $comment_sum_week,
            'searchendtime' => $search_endtime,
        ]);
     }

//  DBから1日分のツイッターコメントを取り出す
    public function daycomment()
    {
        $now = Carbon::now();
        $subday = Carbon::now()->subDay();

        $crypto_comment = new CryptoComment;
        $search_endtime = $crypto_comment->orderBy('id', 'desc')->select('search_endtime')->first();

        $keywords = ["BTC","ETH","ETC","LSK","FCT","XRP","XEM","LTC","BCH","MONA","XLM","QTUM"];
        $Crypto_Num= count($keywords);

        for ($i = 0; $i <= $Crypto_Num-1; $i++) {
             $comment_sum_day[$keywords[$i]] = (int)$crypto_comment->whereBetween('search_endtime',[$subday,$now])->sum($keywords[$i]);
        };

        return response()->json([
            'daycomment' => $comment_sum_day,
            'searchendtime' => $search_endtime,
        ]);
    }

//  DBから1時間分のツイッターコメントを取り出す
    public function hourcomment()
    {
        $now = Carbon::now();
        $subhour = Carbon::now()->subHours();

        $crypto_comment = new CryptoComment;
        $search_endtime = $crypto_comment->orderBy('id', 'desc')->select('search_endtime')->first();

        $keywords = ["BTC","ETH","ETC","LSK","FCT","XRP","XEM","LTC","BCH","MONA","XLM","QTUM"];
        $Crypto_Num= count($keywords);

        for ($i = 0; $i <= $Crypto_Num-1; $i++) {
             $comment_sum_hour[$keywords[$i]] = (int)$crypto_comment->whereBetween('search_endtime',[$subhour,$now])->sum($keywords[$i]);
        };

        return response()->json([
            'hourcomment' => $comment_sum_hour,
            'searchendtime' => $search_endtime,
        ]);
    }
    
//  DBからTwitterユーザーデータをすべて取得（鍵垢などでデータが得られていないものは外す）
    public function twitterusershow()
    {
        $twitter_user = new TwitterUser;
        $user_nofollowing_account = $twitter_user->orderBy('id', 'asc')->where('user_name','!=', 1)->paginate(15);
        
        return response()->json([
             'user_nofollowing_account' => $user_nofollowing_account,
         ]);
    }
}
