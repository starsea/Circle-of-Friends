<?php

use Local\Cache\RedisClient;

class UserModel
{

    public static function getUserInfo($uid)
    {
        $ssdb = RedisClient::getConnection('slave');
        return $ssdb->hGetAll('uid:' . $uid);
    }
}
