<?php

use Local\Cache\Redis;
use Local\Cache\RedisManager;
use Yaf\Registry;

class StatusesController extends Yaf\Controller_Abstract
{


    public function init()
    {
        $this->redis = RedisManager::getConnection('master');
    }

    /**
     * @desc 添加tweet
     *
     */
    public function addTweetAction()
    {

        $redis = RedisManager::getConnection('master');

        $uid   = $this->getRequest()->getPost('uid');
        $tweet = $this->getRequest()->getPost('tweet');
        $time  = $_SERVER['REQUEST_TIME'];

        if (1) {

        }

        $tid = $redis->incr('tid'); // autoincrement id

        $data = array(
            'uid'        => $uid,
            'tweet'      => $tweet,
            'createTime' => $time,
        );

        $redis->hMset('tweet:' . $tid, $data);
        $redis->lPush('myTweet:' . $uid, $tid); // 自己的记录
        $redis->zAdd('timeLine:' . $uid, $time, $tid); // 时间线

        $this->addTweetToFollowers($uid, $tid);


    }

    /**
     * @desc push tweet to all followers
     */

    public function  addTweetToFollowers($uid, $tid)
    {
        $followers = $this->redis->sMembers('followers:' . $uid);

        if ($followers) {

            $this->redis->pipeline();
            foreach ($followers as $fuid) {
                $this->redis->zAdd('timeLine:' . $fuid, $_SERVER['REQUEST_TIME'], $tid);
            }
            $this->redis->exec();
        }
    }


}