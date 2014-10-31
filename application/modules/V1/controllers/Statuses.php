<?php

use Local\Cache\RedisClient;
use Utility\Alias;
use Utility\Validator;
use Config\RedisKey;


class StatusesController extends Yaf\Controller_Abstract
{


    public function createAction()
    {

        $uid   = $this->getRequest()->getPost('uid');
        $tweet = $this->getRequest()->getPost('tweet');
        $time  = $_SERVER['REQUEST_TIME'];

        if (Validator::isEmpty($this->getRequest()->getPost())) {

            Utility\ApiResponse::paramsError();
        }

//        $redis = RedisClient::getConnection('master');
        $cache = RedisClient::getConnection('master');

        $tid = $cache->incr('tid'); // autoincrement id

        $data = array(
            'uid'        => $uid,
            'tweet'      => $tweet,
            'createTime' => $time,
            'tid'        => $tid,
        );


        $ret = $cache->hMset('tweet:' . $tid, $data) &&
            $cache->lPush(RedisKey::userRecord($uid), $tid) &&
            $cache->zAdd(RedisKey::homeTimeLine($uid), $time, $tid);


        //  $this->pushTweetToFollowers($uid, $tid); // 后期考虑放入 backend

        $ret ? Utility\ApiResponse::ok() : Utility\ApiResponse::fail();

    }


    // 根据uid 获取 某人的发帖记录 redis 协议
    // 1500 rps
    public function userRecordAction()
    {
        $uid   = $this->getRequest()->getQuery('uid');
        $limit = $this->getRequest()->getQuery('length', 10) - 1;

        Validator::isEmpty($this->getRequest()->getQuery()) && Utility\ApiResponse::paramsError();


        $cache = RedisClient::getConnection('slave'); // 从也可以写 但是任何写操作不会同步

        $key  = RedisKey::userRecord($uid);
        $tids = $cache->lRange($key, 0, $limit); // list

        // get
        $cache->pipeline();
        foreach ($tids as $tid) {
            $cache->hgetall('tweet:' . $tid);
        }
        $topic = $cache->exec();

        Utility\ApiResponse::ok($topic);
    }

    //个人主页 带评论 800rps todo change zset to list ??
    public function homeTimeLineAction()
    {
        $uid   = $this->getRequest()->getQuery('uid');
        $limit = $this->getRequest()->getQuery('length', 10) - 1;

        Validator::isEmpty($this->getRequest()->getQuery()) && Utility\ApiResponse::paramsError();


        $cache = RedisClient::getConnection('slave'); // 从也可以写 但是任何写操作不会同步

        $key  = RedisKey::homeTimeLine($uid);
        $tids = $cache->zRevRange($key, 0, $limit); // tid=>time  zset
        // var_dump($rank);exit;
        // $tids = array_keys($rank);

        // get
        $cache->pipeline();
        foreach ($tids as $tid) {
            $cache->hgetall('tweet:' . $tid);
        }
        $topic = $cache->exec();

        $cache->pipeline();
        foreach ($tids as $tid) {
            $cache->lrange('reply:' . $tid, 0, -1);
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

}