<?php

use Local\Cache\RedisClient;
use Utility\Alias;
use Utility\Validator;
use Config\RedisKey;


class CommentsController extends Yaf\Controller_Abstract
{


    //回复评论
    public function replyAction()
    {
        $uid          = $this->getRequest()->getPost('uid'); // 谁发出的回复
        $replyUid     = $this->getRequest()->getPost('replyUid'); //回复谁
        $tweetId      = $this->getRequest()->getPost('tweetId'); // 主题id
        $replyContent = $this->getRequest()->getPost('replyContent'); // 回复内容

        Validator::isEmpty(array($uid, $replyUid, $tweetId, $replyContent)) && Utility\ApiResponse::paramsError();


        $ssdb = RedisClient::getConnection('master');

        $rid = str_pad($ssdb->incr('rid'), 15, 0, STR_PAD_LEFT); // for sort

        $data = array(
            'rid'          => $rid,
            'uid'          => $uid,
            'replyUid'     => $replyUid,
            'tweetId'      => $tweetId,
            'replyContent' => $replyContent,
            'time'         => $_SERVER['REQUEST_TIME'],

        );

        $ret = $ssdb->hset('reply:' . $tweetId, $rid, json_encode($data));

        $ret ? Utility\ApiResponse::ok() : Utility\ApiResponse::fail();

    }

    //删除
    public function delAction()
    {
    }

    public function createAction()
    {
    }


}