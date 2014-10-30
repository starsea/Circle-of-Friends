<?php

use Local\Cache\RedisClient;
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

    // 根据uid 获取 某人的发帖记录 redis 协议
    public function userRecordAction()
    {
        $uid   = $this->getRequest()->getQuery('uid');
        $limit = $this->getRequest()->getQuery('length', 10);

        Validator::isEmpty($this->getRequest()->getQuery()) && Utility\ApiResponse::paramsError();


        $cache = RedisClient::getConnection('slave'); // 从也可以写 但是任何写操作不会同步

        $key  = RedisKey::getUserRecord($uid);
        $tids = $cache->lRange($key, 0, $limit); // list

        // get
        $cache->pipeline();
        foreach ($tids as $tid) {
            $cache->hgetall('tweet:' . $tid);
        }
        $topic = $cache->exec();


        Utility\ApiResponse::ok($topic);
    }

    //个人主页
    public function homeTimeLineAction()
    {
        $uid   = $this->getRequest()->getQuery('uid');
        $limit = $this->getRequest()->getQuery('length', 10);

        Validator::isEmpty($this->getRequest()->getQuery()) && Utility\ApiResponse::paramsError();


        $cache = RedisClient::getConnection('slave'); // 从也可以写 但是任何写操作不会同步

        $key  = RedisKey::getHomeTimeLine($uid);
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