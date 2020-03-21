<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserFollowList extends Model
{
    protected $table = 'users_follow_list';
    
    protected $fillable = ['user_id', 'twitter_id', 'follow_details'];
    
    public function twitteruser()
    {
//    return $this->hasMany('App\User');
    return $this->belongsTo('App\TwitterUser');
    }
}
