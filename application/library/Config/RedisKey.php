<?php

namespace Config;

class RedisKey
{

    const HOME_TIME_LINE = 'home_time_line:';

    const USER_RECORD = 'user_record:';

    // 获取用户发布的微博
    public static function userRecord($uid)
    {
        return self::USER_RECORD . $uid;

    }


    // 获取当前登录用户及其所关注用户的最新微博
    public static function homeTimeLine($uid)
    {
        return self::HOME_TIME_LINE . $uid;
    }

    public static function friendsList($uid)
    {
        return 'friends:uid:' . $uid;
    }


}