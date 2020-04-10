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

       // $flash_message = 'フォロー済みのアカウントです。';
       //
       //       return response()->json([
       //          'message' => $flash_message,
       //         ]);

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

//            dump(property_exists($searchUser, 'id'));

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

        $twitter_user = new TwitterUser;
        $twitter_table_id = $twitter_user->where('screen_name',$name)->select('id')->first();

        $user_follow_list = new UserFollowList;
        $user_follow_list->where('user_id',Auth::id())->where('twitter_id',$twitter_table_id->id)->update(['follow_details' => 'following']);

        return response()->json(['message' => $flash_message]);
    }



    public function reload()
    {
        // ログイン中のユーザーがAPI制限中でないことをDBのapi_limit_timeから確認
        $api_limit_time = Auth::user()->where('id',Auth::id())->select('api_limit_time')->first();
        $now = Carbon::now();

        if ($now < $api_limit_time->api_limit_time){
            // return redirect('http://127.0.0.1:8000/mypage')->with('flash_message', 'API制限中のためしばらくお待ちください。');
            return response()->json([
                 'message' => "API制限中のため15minお待ちください",
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

            if (!is_array($follow)){
                return response()->json([
                     'message' => "API制限中のため15minお待ちください",
                 ]);
                // return redirect('/mypage')->with('flash_message', 'API制限中のためしばらくお待ちください。');
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

        return response()->json([
             'user_nofollowing_account' => $user_nofollowing_account,
             'message' => "再読み込みしました。",
         ]);
    }



    public function autofollow(Request $request)
    {
        // ログイン中のユーザーがAPI制限中でないことをDBのapi_limit_timeから確認
        $api_limit_time = Auth::user()->where('id',Auth::id())->select('api_limit_time')->first();
        $now = Carbon::now();

        if ($now < $api_limit_time->api_limit_time){
            return response()->json([
                 'message' => 'API制限中のためしばらくお待ちください',
             ]);
        };

//        return response()->json([
//            'message' => "API制限中のため{$api_limit_time->api_limit_time}"."\n"."お待ちください",
//        ]);

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


        $name = $request->input('action');
        $follow_count = 0;
        $follow_max = count($name);

//        var_dump (count($name));

        for ($i = 0; $i < count($name); $i++) {

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
                     'message' => "{$follow_max}件中{$follow_count}件フォローしました。API制限中のため15minお待ちください",
                 ]);
            };

            // フォロー前に二重フォローを防ぐためにユーザーとの関係を再確認
            $follow = $twitter_login_user->get('friendships/lookup', array(
              'screen_name' => $name[$i],
            ));

//            var_dump ($api_limit_num);
//            var_dump($follow[0]->connections[0]);

            if($follow[0]->connections[0] === 'none' || $follow[0]->connections[0] === 'followed_by'){
                #フォローする
                $searchUser = $twitter_login_user->post('friendships/create',array(
                       'screen_name' => $name[$i],
                ));

                if(!property_exists($searchUser, 'id')){
                    $api_limit_time = Carbon::now()->addminute(60);
                    Auth::user()->where('id',Auth::id())->update(['api_limit_time' => $api_limit_time]);

                    return response()->json([
                     'message' => "{$follow_max}件中{$follow_count}件フォローしました。フォロー制限のため1時間お待ちください",
                    ]);
                };

            };
                $twitter_user = new TwitterUser;
                $twitter_table_id = $twitter_user->where('screen_name',$name[$i])->select('id')->first();

                $user_follow_list = new UserFollowList;
                $user_follow_list->where('user_id',Auth::id())->where('twitter_id',$twitter_table_id->id)->update(['follow_details' => 'following']);

                $follow_count = $follow_count + 1;

        };

        $flash_message = "{$follow_max}件、すべてのユーザーをフォローしました";

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

}
