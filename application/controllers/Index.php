<?php

/**
 * @name IndexController
 * @author haidx
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */

use Local\Cache\Redis;
use Local\Cache\RedisClient;

class IndexController extends Yaf\Controller_Abstract
{

    /**
     * 默认动作
     * Yaf支持直接把Yaf_Request_Abstract::getParam()得到的同名参数作为Action的形参
     * 对于如下的例子, 当访问http://yourhost/sample/index/index/index/name/haidx 的时候, 你就会发现不同
     */
    public function indexAction($name = "Stranger")
    {
        echo 'hello ,world';
        //1. fetch query
        $get = $this->getRequest()->getQuery("get", "default value");

        //2. fetch model
        $model = new SampleModel();

        //3. assign
        $this->getView()->assign("content", $model->selectSample());
        $this->getView()->assign("name", $name);

        //4. render by Yaf, 如果这里返回FALSE, Yaf将不会调用自动视图引擎Render模板
        return TRUE;
    }

    // 测试 redis
    public function testAction()
    {
        $redis = RedisClient::getConnection(); // default 127.0.0.1:6379
        var_dump($redis->get(98130));

        exit;
    }

    public function testRedisAction()
    {
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);

        $rand = rand(1, 100000);

        $redis->test('zkey', array($rand, $rand), 100);

        exit;
    }

    /*
     * 返回的既然是一唯数组
     */
    public function sortAction()
    {
//    sort test1:1 by score desc  get #  get user_info_*->score get user_info_*->nickname


        $redis = RedisClient::getConnection();
        $ret   = $redis->sort('test1:1', array(
            'by'   => 'score',
            'get'  => array('#', 'user_info_*->score', 'user_info_*->nickname'),
            'key'  => array('uid', 'score', 'nickname'),
#            'get'  => array('#'),
            'sort' => 'desc'
        ));

        var_dump($ret);
    }


}
