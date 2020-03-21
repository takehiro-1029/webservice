<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TwitterUser extends Model
{
    protected $table = 'twitter_users';
    
    protected $fillable = ['account_id', 'user_name', 'screen_name', 'profile_image_url', 'description', 'follows_count', 'friends_count', 'recent_tweet', 'day_update_flg', 'delete_flg', 'created_at', 'updated_at'];
    
    public function userfollowlist()
    {
        return $this->hasMany('App\UserfollowList');
    }
}
