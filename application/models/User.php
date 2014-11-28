<?php

use Local\Cache\RedisClient;
use Pyramid\Component\Password\Password;

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
     * @desc 返回信息不包含密码 包含 uid
     * @param $userInfo
     * @return int|bool
     */
    public static function register($userInfo)
    {
        $userInfo['password'] = Password::hash($userInfo['password']);

        $uid = db_insert('user')->fields($userInfo)->execute();

        unset($userInfo['password']);
        $userInfo['uid'] = $uid;

        return $uid ? $userInfo : false;
    }


    public static function setToken($uid)
    {

        return \Utility\Cookie::set('token', $uid);
    }


    public static function getUidByToken()
    {
        var_dump($_COOKIE['token']);
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


        if ($userInfo && Password::verify($password, $userInfo['password'])) {

            self::updateLoginTime($userInfo['uid']);

            return self::getUserBasicInfo($userInfo);
        }
        return false;

    }

    public static function updateLoginTime($uid)
    {
        db_update('user')->fields(array('login_time' => date('Y-m-d H:i:s', time())))->condition('uid', $uid)->execute();

    }


    public static function  getUserBasicInfo($userInfo)
    {
        return array(
            'uid'      => $userInfo['uid'],
            'username' => $userInfo['username'],
//            'login_time' => $userInfo['login_time'],

        );
    }


    public static function isLogin()
    {
        $uid = self::getUidByToken();
        return $uid ? $uid : false;
    }
}
