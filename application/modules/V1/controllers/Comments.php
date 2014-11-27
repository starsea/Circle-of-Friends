<?php

use Local\Cache\RedisClient;
use Utility\Alias;
use Utility\Validator;
use Config\RedisKey;


class CommentsController extends Yaf\Controller_Abstract
{


    //回复评论 需要登录
    public function replyAction()
    {

        if (!$uid = UserModel::isLogin()) {
            Utility\ApiResponse::notLogin();
        }

        $replyUid     = $this->getRequest()->getPost('replyUid'); //回复谁
        $tid          = $this->getRequest()->getPost('tid'); // 主题id
        $replyContent = $this->getRequest()->getPost('replyContent'); // 回复内容

        Validator::isEmpty(array($uid, $replyUid, $tid, $replyContent)) && Utility\ApiResponse::paramsError();


        $ssdb = RedisClient::getConnection('master');

        $rid = $ssdb->incr('rid');
//        $rid = str_pad($ssdb->incr('rid'), 15, 0, STR_PAD_LEFT); // for sort

        $data = array(
            'rid'          => $rid,
            'uid'          => $uid,
            'replyUid'     => $replyUid,
            'tid'          => $tid,
            'replyContent' => $replyContent,
            'time'         => $_SERVER['REQUEST_TIME'],

        );

        $ssdb->zAdd('reply:index:' . $tid, $rid, $rid); // 为了方便以后评论分页 还是采用 zset 索引比较好
        $ret = $ssdb->hset('reply:' . $tid, $rid, json_encode($data)); // hash json 取出为 o(n)  如果采用 m 个 hash array 那么就是 o（n*m）


        // 消息列表

        $ret ? Utility\ApiResponse::ok() : Utility\ApiResponse::fail();

    }

    //删除
    public function delAction()
    {

    }


    //获取某条微博的评论列表
    public function showAction()
    {
        $tid  = $this->getRequest()->getQuery('tid');
        $ssdb = RedisClient::getConnection('master');


        $data = $ssdb->hGetAll('reply:' . $tid);

        $data ? Utility\ApiResponse::ok($data) : Utility\ApiResponse::fail();

    }


}