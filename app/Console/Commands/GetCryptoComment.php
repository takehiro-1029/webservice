<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Abraham\TwitterOAuth\TwitterOAuth;
use Carbon\Carbon;
use App\CryptoComment;

class GetCryptoComment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:getcryptocomment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'counting twitter cryptocomment for 5minuits';

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
//      twitterにアクセスする
        $twitter = new TwitterOAuth(
            config('services.twitter.client_id'),
            config('services.twitter.client_secret'),
            config('services.twitter.access_token'),
            config('services.twitter.access_token_secret')
          );
        $keywords = ["#BTC", "#ETH", "#ETC","#LSK","#FCT","#XRP","#XEM","#LTC","#BCH","#MONA","#XLM","#QTUM"];
        $sincetime = date('Y-m-d_H:i:s', strtotime("now -5 min"));
        $untiltime = date('Y-m-d_H:i:s');

        $starttime = Carbon::now()->subMinutes(5)->toDateTimeString();
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
    }
}
