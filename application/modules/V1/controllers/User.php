<?php

use Local\Cache\RedisClient;
use Utility\Alias;
use Utility\Validator;
use Config\RedisKey;


class UserController extends Yaf\Controller_Abstract
{

    public function registerAction()
    {
        $username = $this->getRequest()->getPost('username');
        $password = $this->getRequest()->getPost('password');
        $mac      = $this->getRequest()->getPost('mac');


        if (Validator::isEmpty(array($username, $password, $mac))) {

            Utility\ApiResponse::paramsError();
        }

        if (UserModel::isRepeatRegister($username)) {
            Utility\ApiResponse::fail('username had exists');
        }

        $userInfo = array(
            'username'      => $username,
            'password'      => $password,
            'mac'           => $mac,
            'register_time' => date('Y-m-d H:i:s', time()),
        );

        $ret = false;

        if ($uid = UserModel::register($userInfo)) {

            $ret = UserModel::setUserInfoToSSDB($uid, $userInfo) && UserModel::setToken($uid);
        }

        $ret ? Utility\ApiResponse::ok($userInfo) : Utility\ApiResponse::fail();

    }

    public function loginAction()
    {
        $username = $this->getRequest()->getPost('username');
        $password = $this->getRequest()->getPost('password');
        $mac      = $this->getRequest()->getPost('mac');

        if (Validator::isEmpty(array($username, $password, $mac))) {

            Utility\ApiResponse::paramsError();
        }

        if ($userInfo = UserModel::verifyLogin($username, $password)) {

            UserModel::setToken($userInfo['uid']);
            Utility\ApiResponse::ok($userInfo);

        } else {
            Utility\ApiResponse::fail('username or password error');
        }


    }

    public function logoutAction()
    {
        echo UserModel::getUidByToken();
    }


    public function oauthLogin()
    {

    }


}