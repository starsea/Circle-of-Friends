<?php

use Local\Cache\RedisManager;
use Utility\Alias;
use Utility\Validator;
use Local\Cache\SSDBClient;
use Config\RedisKey;


class StatusesController extends Yaf\Controller_Abstract
{


    public function init()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        ini_set('memory_limit', '-1');
        set_time_limit(0);
    }
    // 发表消息接口
    // todo 图片
    public function addAction()
    {

        $uid   = $this->getRequest()->getPost('uid');
        $tweet = $this->getRequest()->getPost('tweet');
        $time  = $_SERVER['REQUEST_TIME'];

        if (Validator::isEmpty($this->getRequest()->getPost())) {

            Utility\ApiResponse::paramsError();
        }

//        $redis = RedisManager::getConnection('master');
        $cache = SSDBClient::getConnection('master');

        $tid = $cache->incr('tid'); // autoincrement id

        $data = array(
            'uid'        => $uid,
            'tweet'      => $tweet,
            'createTime' => $time,
            'tid'        => $tid,
        );


        $ret = $cache->multi_hset('tweet:' . $tid, $data) &&
            $cache->qpush_front(RedisKey::getUserRecord($uid), $tid) &&
            $cache->zAdd(RedisKey::getHomeTimeLine($uid), $time, $tid);


        //  $this->pushTweetToFollowers($uid, $tid); // 后期考虑放入 backend

        $ret ? Utility\ApiResponse::ok() : Utility\ApiResponse::fail();

    }

    public function delAction()
    {

    }

    //回复主题
    public function replyAction()
    {
        $uid          = $this->getRequest()->getPost('uid'); // 谁发出的回复
        $replyUid     = $this->getRequest()->getPost('replyUid'); //回复谁
        $tweetId      = $this->getRequest()->getPost('tweetId'); // 主题id
        $replyContent = $this->getRequest()->getPost('replyContent'); // 回复内容

        Validator::isEmpty(array($uid, $replyUid, $tweetId, $replyContent)) && Utility\ApiResponse::paramsError();


        $data = array(
            'uid'          => $uid,
            'replyUid'     => $replyUid,
            'tweetId'      => $tweetId,
            'replyContent' => $replyContent,
            'time'         => $_SERVER['REQUEST_TIME'],

        );

        $ret = SSDBClient::getConnection('master')->lPush('reply:' . $tweetId, json_encode($data));

        $ret ? Utility\ApiResponse::ok() : Utility\ApiResponse::fail();


    }

    public function indexAction()
    {
        echo date("Y-m-d H:i:s",time());
    }

    //todo 申请朋友
    public function applyFriend()
    {

    }

    //todo 接受朋友请求
    public function acceptFriend()
    {

    }

    //todo 获取当前登录用户及其所关注用户的最新微博
    public function homeTimeLineAction()
    {
        $uid   = $this->getRequest()->getQuery('uid');
        $limit = $this->getRequest()->getQuery('length', 10);

        Validator::isEmpty($this->getRequest()->getQuery()) && Utility\ApiResponse::paramsError();


        $cache = SSDBClient::getConnection('slave'); // 从也可以写 但是任何写操作不会同步

        $key  = RedisKey::getHomeTimeLine($uid);
        $rank = $cache->zRevRange($key, 0, $limit); // tid=>time  zset
        $tids = array_keys($rank);

        // get
        $cache->batch();
        foreach ($tids as $tid) {
            $cache->hgetall('tweet:' . $tid);
        }
        $topic = $cache->exec();

        $cache->batch();
        foreach ($tids as $tid) {
            $cache->qrange('reply:' . $tid, 0, -1);
        }
        $reply = $cache->exec();

        //var_dump($reply);

        $data = array_map(function ($a, $b) {

            return array(
                'topic' => $a,
                'reply' => json2Array($b)
            );

        }, $topic, $reply);


        Utility\ApiResponse::ok($data);

    }

    // 根据uid 获取 某人的发帖记录
    public function userRecordAction()
    {
        $uid   = $this->getRequest()->getQuery('uid');
        $limit = $this->getRequest()->getQuery('length', 10);

        Validator::isEmpty($this->getRequest()->getQuery()) && Utility\ApiResponse::paramsError();


        $cache = SSDBClient::getConnection('slave'); // 从也可以写 但是任何写操作不会同步

        $key  = RedisKey::getUserRecord($uid);
        $tids = $cache->qrange($key, 0, $limit); // list

        // get
        $cache->batch();
        foreach ($tids as $tid) {
            $cache->hgetall('tweet:' . $tid);
        }
        $topic = $cache->exec();

//
//        $cache->batch();
//        foreach ($tids as $tid) {
//            $cache->qrange('reply:' . $tid, 0, -1);
//        }
//        $reply = $cache->exec();
//
//        //var_dump($reply);
//
//        $data = array_map(function ($a, $b) {
//
//            return array(
//                'topic' => $a,
//                'reply' => json2Array($b)
//            );
//
//        }, $topic, $reply);


        Utility\ApiResponse::ok($topic);
    }


}