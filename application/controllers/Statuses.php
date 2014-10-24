<?php

use Local\Cache\RedisManager;
use Utility\Alias;
use Utility\Validator;
use Local\Cache\SSDBClient;

class StatusesController extends Yaf\Controller_Abstract
{


    public function init()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    }
    // 发表消息接口
    // todo 图片
    public function ssdbAction()
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
        );


        $ret = $cache->multi_hset('tweet:' . $tid, $data) &&
            $cache->lpush('myTweet:' . $uid, $tid) &&
            $cache->zAdd('timeLine:' . $uid, $time, $tid);


        //  $this->pushTweetToFollowers($uid, $tid); // 后期考虑放入 backend

        $ret ? Utility\ApiResponse::ok() : Utility\ApiResponse::fail();

    }

    //回复主题
    public function replyAction()
    {
        $uid          = $this->getRequest()->getPost('uid'); // 谁发出的回复
        $replyUid     = $this->getRequest()->getPost('replyUid'); //回复谁
        $tweetId      = $this->getRequest()->getPost('tweetId'); // 主题id
        $replyContent = $this->getRequest()->getPost('replyContent'); // 回复内容

        Validator::isEmpty($this->getRequest()->getPost()) && Utility\ApiResponse::paramsError();


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

    //申请朋友
    public function applyFriend()
    {

    }

    // 接受朋友请求
    public function acceptFriend()
    {

    }

    public function testZsetAction()
    {
        ini_set('memory_limit', '-1');

        $master = SSDBClient::getConnection('master');

        $t1 = Alias::microtime_float();
        $master->batch();

        for ($i = 1; $i <= 100000; $i++) {
            $master->zadd('zkey', mt_rand(1, 10000), $i);
        }
        $master->exec();

        $t2 = Alias::microtime_float();

        echo $t2 - $t1;


    }


}