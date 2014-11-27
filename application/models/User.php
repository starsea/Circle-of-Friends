<?php

use Local\Cache\RedisClient;

class UserModel
{

    public static function getUserInfo($uid)
    {
        $ssdb = RedisClient::getConnection('slave');
        return $ssdb->hGetAll('uid:' . $uid);
    }


    public static function setUserInfoToSSDB($uid, Array $userInfo)
    {
        $ssdb = RedisClient::getConnection('master');

        $userInfo['uid'] = $uid;
        unset($userInfo['password']);

        return $ssdb->hMset('uid:' . $uid, $userInfo);
    }


    public static function isRepeatRegister($username)
    {
        $data = db_select('user', 'alias')
            ->fields('alias')
            ->condition('username', $username)
            ->execute()
            ->fetchAssoc();

        return $data ? true : false;

    }

    /**
     * @param $userInfo
     * @return int|bool
     */
    public static function register($userInfo)
    {
        $ret = db_insert('user')->fields($userInfo)->execute();

        return $ret ? $ret : false;
    }


    public static function setToken($uid)
    {

        return \Utility\Cookie::set('token', $uid);
    }


    public static function getUidByToken()
    {

        $token = \Utility\Cookie::get('token');
        return (int)$token;

    }


    public static function test()
    {
        $data = db_select('user', 'alias', array('target' => 'master'))
            ->fields('alias')
            ->condition('username', 'xxx')
            ->execute();

        var_dump($data);
    }

    /**
     * @param $username
     * @param $password
     * @return bool|array
     */
    public static function verifyLogin($username, $password)
    {
        $userInfo = db_select('user', 'alias', array('target' => 'master'))
            ->fields('alias')
            ->condition('username', $username)
            ->execute()
            ->fetchAssoc();

        // todo password salt
        if ($userInfo && $userInfo['password'] == $password) {

            return self::filterUserInfo($userInfo);
        }
        return false;

    }

    public static function filterUserInfo($userInfo)
    {
        unset($userInfo['password']);
        return $userInfo;
    }


    public static function isLogin()
    {
        $uid = self::getUidByToken();
        return $uid ? $uid : false;
    }
}
