<?php

use Local\Cache\RedisManager;
use Utility\Alias;
use Utility\Validator;
use Local\Cache\SSDBClient;

class StatusesController extends Yaf\Controller_Abstract
{


    public function ssdbAction()
    {
        error_reporting(E_ALL);
        $uid   = $this->getRequest()->getPost('uid');
        $tweet = $this->getRequest()->getPost('tweet');
        $time  = $_SERVER['REQUEST_TIME'];

        if (Validator::isEmpty(array('uid', 'tweet'))) {

            echo json_encode(array(
                'ret' => -1,
                'msg' => 'invalid params',
            ));
            exit;
        }

//        $redis = RedisManager::getConnection('master');
        $cache = SSDBClient::getConnection('default');

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

        if ($ret) {
            echo json_encode(array(
                'ret'  => 0,
                'msg'  => 'ok',
                'data' => 'todo',
            ));
            exit;
        }

    }

    /**
     * @desc push tweet to all followers
     */

    public function  pushTweetToFollowers($uid, $tid)
    {
        $redis = Alias::redis('master');

        $followers = $redis->sMembers('followers:' . $uid);

        if ($followers) {

            $redis->pipeline();
            foreach ($followers as $fuid) {
                $redis->zAdd('timeLine:' . $fuid, $_SERVER['REQUEST_TIME'], $tid);
            }
            $redis->exec();
        }
    }


}