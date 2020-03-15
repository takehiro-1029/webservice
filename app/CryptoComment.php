<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CryptoComment extends Model
{
    protected $table = 'crypto_comments';
    
    protected $fillable = ['BTC', 'ETH', 'ETC', 'LSK', 'FCT', 'XRP', 'XEM', 'LTC', 'BCH', 'MONA', 'XLM', 'QTUM', 'search_starttime','search_endtime'];
}
