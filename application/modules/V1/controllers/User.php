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

        $registerInfo = array(
            'username'      => $username,
            'password'      => $password,
            'mac'           => $mac,
            'register_time' => date('Y-m-d H:i:s', time()),
            'login_time'    => date('Y-m-d H:i:s', time()),
        );

        $ret = false;

        if ($userInfo = UserModel::register($registerInfo)) {

            $uid = $userInfo['uid'];
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
        \Utility\Cookie::delete('token');

        \Utility\ApiResponse::ok();
    }


    /**
     * @desc 第三方登录
     *
     */
    public function oauthLoginAction()
    {
        $openid   = $this->getRequest()->getPost('openid');
        $username = $this->getRequest()->getPost('username');
        $type     = $this->getRequest()->getPost('type');
        $mac      = $this->getRequest()->getPost('mac');


        if (!in_array($type, array('qq', 'sina', 'weixin'))) {
            Utility\ApiResponse::fail('oauth is error');
        }

        $field = 'openid_' . $type;

        if ($userInfo = UserModel::exists($field, $openid)) {

            // login
            UserModel::setToken($userInfo['uid']);

            Utility\ApiResponse::ok($userInfo);

        } else {
            // 新账号注册
            $registerInfo = array(
                'username'      => $username,
                'mac'           => $mac,
                'register_time' => date('Y-m-d H:i:s', time()),
                'login_time'    => date('Y-m-d H:i:s', time()),
                $field          => $openid,
            );
            $userInfo     = UserModel::register($registerInfo);

            $uid = $userInfo['uid'];
            UserModel::setUserInfoToSSDB($uid, $userInfo);
            UserModel::login($uid);

            Utility\ApiResponse::ok($userInfo);

        }


    }

    public function testAction()
    {
        echo 'uid: ' . UserModel::getUidByToken();
        $password = '';
        $hash     = \Pyramid\Component\Password\Password::hash($password);

        var_dump($password);
        var_dump(\Pyramid\Component\Password\Password::verify('', $hash));

    }


}