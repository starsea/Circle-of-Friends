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

        if (Validator::isEmpty(array($uid, $tweet))) {

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


        $ret = $cache->hMset(RedisKey::tweets($tid), $data) &&
            $cache->zAdd(RedisKey::userRecord($uid), $tid, $tid) &&
            $cache->zAdd(RedisKey::homeTimeLine($uid), $tid, $tid);


        //  $this->pushTweetToFollowers($uid, $tid); // 后期考虑放入 backend

        $ret ? Utility\ApiResponse::ok() : Utility\ApiResponse::fail();

    }


    // 根据uid 获取 某人的发帖记录 redis 协议
    // 1500 rps
    public function userRecordAction()
    {
        $uid    = $this->getRequest()->getQuery('uid');
        $start  = $this->getRequest()->getQuery('start', '+inf');
        $limit  = $this->getRequest()->getQuery('limit', 10);
        $end    = '-inf';
        $offset = ($start === '+inf') ? 0 : 1;


        Validator::isEmpty(array($uid)) && Utility\ApiResponse::paramsError();


        $cache = RedisClient::getConnection('slave'); // 从也可以写 但是任何写操作不会同步

        $key  = RedisKey::userRecord($uid);
//        $tids = $cache->zRevRange($key, $start, $end);
        $tids = $cache->zRevRangeByScore($key, $start, $end, array('limit' => array($offset, $limit)));


        // get
        $cache->pipeline();
        foreach ($tids as $tid) {
            $cache->hgetall(RedisKey::tweets($tid));
        }
        $topic = $cache->exec();

        Utility\ApiResponse::ok($topic);
    }

    //个人主页 带评论 1000rps
    public function homeTimeLineAction()
    {
        $uid    = $this->getRequest()->getQuery('uid');
        $start  = $this->getRequest()->getQuery('start', '+inf');
        $limit  = $this->getRequest()->getQuery('limit', 10);
        $end    = '-inf';
        $offset = ($start === '+inf') ? 0 : 1;

        Validator::isEmpty(array($uid)) && Utility\ApiResponse::paramsError();


        $cache = RedisClient::getConnection('slave'); // 从也可以写 但是任何写操作不会同步

        $key  = RedisKey::homeTimeLine($uid);
        $tids = $cache->zRevRangeByScore($key, $start, $end, array('limit' => array($offset, $limit)));

        // topic
        $cache->pipeline();
        foreach ($tids as $tid) {
            $cache->hgetall(RedisKey::tweets($tid));
        }
        $topic = $cache->exec();

        // reply
        $cache->pipeline();
        foreach ($tids as $tid) {
            $cache->hGetAll('reply:' . $tid);
        }
        $reply = $cache->exec();


        $content = array_map(function ($a, $b) {

            return array(
                'topic' => $a,
                'reply' => json2Array($b)
            );

        }, $topic, $reply);

        $data = array(
            'self'    => UserModel::getUserInfo($uid),
            'content' => $content,

        );

//var_dump($data);
        Utility\ApiResponse::ok($data);

    }

    public function indexAction()
    {
        echo 1;
    }

}