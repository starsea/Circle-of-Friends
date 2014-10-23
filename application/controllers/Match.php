<?php

use Local\Cache\SSDBClient;
use Local\Cache\RedisClient;

class MatchController extends Yaf\Controller_Abstract
{
    public function indexAction()
    {
        $redis = new RedisClient();

        $a = $redis->getInstance(array('host' => '127.0.0.1', 'port' => 6379));
        $b = $redis->getInstance(array('host' => '127.0.0.1', 'port' => 6380));

    var_dump($a,$b);
        exit;
//        $redis->zRange()
        $ssdb = new SSDBClient('127.0.0.1', 8888);
        echo 1;
        $uid          = (int)$this->getRequest()->getQuery('uid');
        $matchType    = (int)$this->getRequest()->getQuery('matchType');
        $matchSubType = (int)$this->getRequest()->getQuery('matchSubType');
        $role         = $this->getRequest()->getQuery('role');
        $nickName     = $this->getRequest()->getQuery('nickName');

        $now      = time();
        $userFlag = $uid . KS . $now;
        $userInfo = array(
            'uid'        => $uid,
            'nickName'   => $nickName,
            'role'       => $role,
            'joinTime'   => $now,
            'score'      => 0,
            'uploadTime' => 0,
            'award'      => 0
        );
        $ssdb->multi_hset($uid, $userInfo);

        $queueName = "list:1:1";
        $ret       = $ssdb->qpush_back($queueName, $userFlag);

        if ($ret) {
            $ssdb->incr('GAME_PEOPLE_NUM');

            echo json_encode(array(
                'ret'    => 0,
                'msg'    => 'add queue ' . $queueName . ' success',
                'result' => array('userFlag' => $userFlag)
            ));
            exit;

        } else {
            echo json_encode(array(
                'ret' => 2,
                'msg' => 'add queue ' . $queueName . ' error'
            ));
            exit;
        }
    }

    public function setRoom()
    {

    }


}