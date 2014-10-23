<?php

use Local\Cache\RedisManager;
use Utility\Alias;
use Utility\Validator;

class StatusesController extends Yaf\Controller_Abstract
{

    /**
     * @desc 添加tweet
     *
     */
    public function redisAction()
    {

        $uid   = $this->getRequest()->getPost('uid');
        $tweet = $this->getRequest()->getPost('tweet');

        $image = $this->getRequest()->getPost('img');


        $time = $_SERVER['REQUEST_TIME'];

        if (Validator::isEmpty(array($uid, $tweet))) {

            echo json_encode(array(
                'ret' => -1,
                'msg' => 'invalid params',
            ));
            exit;
        }

//        $redis = RedisManager::getConnection('master');
        $redis = Alias::redis('master');

        $tid = $redis->incr('tid'); // autoincrement id

        $data = array(
            'uid'        => $uid,
            'tweet'      => $tweet,
            'createTime' => $time,
        );

        $ret = $redis->hMset('tweet:' . $tid, $data) &&
            $redis->lPush('myTweet:' . $uid, $tid) &&
            $redis->zAdd('timeLine:' . $uid, $time, $tid);


        // $this->pushTweetToFollowers($uid, $tid); // 后期考虑放入 backend

        if ($ret) {
            echo json_encode(array(
                'ret'  => 0,
                'msg'  => 'ok',
                'data' => 'todo',
            ));

        }
        $redis->close();
        exit;


    }

    public function ssdbAction()
    {
        error_reporting(E_ALL);
        $uid   = $this->getRequest()->getPost('uid', 1);
        $tweet = $this->getRequest()->getPost('tweet', 'fdsf');
        $time  = $_SERVER['REQUEST_TIME'];

        if (0) { //todo check empty params

            echo json_encode(array(
                'ret' => -1,
                'msg' => 'invalid params',
            ));
            exit;
        }

//        $redis = RedisManager::getConnection('master');
        $redis = new \Local\Cache\SSDBClient('127.0.0.1', 8888);

        $tid = $redis->incr('tid'); // autoincrement id

        $data = array(
            'uid'        => $uid,
            'tweet'      => $tweet,
            'createTime' => $time,
        );


        $ret = $redis->multi_hset('tweet:' . $tid, $data) &&
            $redis->qpush('myTweet:' . $uid, $tid) &&
            $redis->zAdd('timeLine:' . $uid, $time, $tid);


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