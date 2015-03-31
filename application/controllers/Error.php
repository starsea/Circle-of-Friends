<?php

/**
 * @name ErrorController
 * @desc 错误控制器, 在发生未捕获的异常时刻被调用
 * @see http://www.php.net/manual/en/yaf-dispatcher.catchexception.php
 * @author haidx
 */
class ErrorController extends Yaf\Controller_Abstract
{

    //从2.1开始, errorAction支持直接通过参数获取异常
    public function errorAction($exception)
    {
        //1. assign to view engine
        //echo $exception->xdebug_message;

//        var_dump($this->getView()->setScriptPath('/usr/local/var/www/yaf/application'));
//        var_dump($this->getView()->getScriptPath());
//        var_dump($this->getViewPath());

//        var_dump($this->getModuleName());


        $params = $this->getRequest()->getParams();

        unset($params['exception']);


        $this->getView()->params = array_merge(
            array(),
            $params,
            $this->getRequest()->getPost(),
            $this->getRequest()->getQuery()
        );

        $view = array(
            "e"       => $exception,
            'e_class' => get_class($exception),

        );
        echo $this->getView()->render($this->getViewPath() . "/error/error.phtml", $view);
    }
}
