<?php

/**
 * @name Bootstrap
 * @author haidx
 * @desc 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * @see http://www.php.net/manual/en/class.yaf-bootstrap-abstract.php
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends \Yaf\Bootstrap_Abstract
{

    public function _initGlobal()
    {
        \Yaf\Loader::import(ROOT_PATH . '/common/function.php');
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        ini_set('memory_limit', '-1');
        set_time_limit(0);

    }


    public function _initConfig()
    {
        //把配置保存起来
        $arrConfig = Yaf\Application::app()->getConfig();
        Yaf\Registry::set('config', $arrConfig);

    }

    /*
     * 初始化 redis 配置
     */
    public function _initRedis()
    {
//        $redises = \Yaf\Registry::get('config')->redis->toArray();
        $redises = \Yaf\Registry::get('config')->ssdb->toArray(); // 把 ssdb 走 redis 协议
        \Local\Cache\RedisClient::parseConnectionInfo($redises);

    }

    /*
     * 初始化 ssdb 配置
     */
    public function _initSSDB()
    {
        $ssdbs = \Yaf\Registry::get('config')->ssdb->toArray();
        \Local\Cache\SSDBClient::parseConnectionInfo($ssdbs);

    }

    public function _initPlugin(Yaf\Dispatcher $dispatcher)
    {
        //注册一个插件
        $objSamplePlugin = new SamplePlugin();
        $dispatcher->registerPlugin($objSamplePlugin);
    }

    public function _initRoute(Yaf\Dispatcher $dispatcher)
    {
        //在这里注册自己的路由协议,默认使用简单路由
    }

    public function _initView(Yaf\Dispatcher $dispatcher)
    {
        Yaf\Dispatcher::getInstance()->autoRender(FALSE); // 关闭模板调用
        //在这里注册自己的view控制器，例如smarty,firekylin
    }
}
