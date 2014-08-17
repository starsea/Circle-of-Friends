<?php

use \Cache\SimpleSSDB;

class MatchController extends Yaf\Controller_Abstract
{
    public function indexAction()
    {
        $ssdb = new SimpleSSDB('127.0.0.1', 8888);

        $uid          = (int)$this->getRequest()->getQuery('uid');
        $matchType    = (int)$this->getRequest()->getQuery('matchType');
        $matchSubType = (int)$this->getRequest()->getQuery('matchSubType');
        $role         = $this->getRequest()->getQuery('role');
        $nickName     = $this->getRequest()->getQuery('nickName');

        $now      = time();
        $userFlag = $uid . KS . $now;
        $userInfo = array(
            'uid'        => $uid,
            'userFlag'   => $userFlag,
            'nickName'   => $nickName,
            'role'       => $role,
            'joinTime'   => $now,
            'score'      => 0,
            'uploadTime' => 0,
            'award'      => 0
        );
        $ssdb->multi_hset($userFlag, $userInfo);

        $queueName = "list:1:1";
        $ret       = $ssdb->lpush($queueName, $userFlag);

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



}