<?php

use Local\Cache\RedisClient;
use Utility\Validator;
use Config\RedisKey;

/**
 * Class FriendshipsController 好友关系控制器
 *
 * @author dengxinghai
 * @version v1
 */
class FriendshipsController extends \Yaf\Controller_Abstract
{

    /**
     * todo 申请好友 需要推送
     */
    public function applyAction()
    {

    }

    /**
     * 接受好友请求 客户端发来
     */
    public function acceptAction()
    {

        $uid1 = $this->getRequest()->getPost('uid1'); // 谁接受了请求
        $uid2 = $this->getRequest()->getPost('uid2'); // 另外一个人

        Validator::isEmpty(array($uid1, $uid2)) && Utility\ApiResponse::paramsError();

        $cache = RedisClient::getConnection('master');

        $ret = $cache->lPush(RedisKey::friendsList($uid1), $uid2) && $cache->lPush(RedisKey::friendsList($uid2), $uid1);

        $ret ? Utility\ApiResponse::ok() : Utility\ApiResponse::fail();
    }

    /**
     * 获取某人所有 朋友 uid
     */
    public function friendsAction()
    {
        $uid = $this->getRequest()->getQuery('uid');
        Validator::isEmpty(array($uid)) && Utility\ApiResponse::paramsError();

        $cache   = RedisClient::getConnection('slave');
        $uidList = $cache->lRange(RedisKey::friendsList($uid), 0, -1);
//        var_dump($uidList);
        Utility\ApiResponse::ok($uidList);
    }
}