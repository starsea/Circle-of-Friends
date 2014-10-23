<?php

#项目根目录
define('ROOT_PATH', dirname(dirname(__FILE__)));

# application path
define('APP_PATH', ROOT_PATH . '/application');

#网站根目录
define('WEB_PATH', dirname(__FILE__));

# 目录分割符
define("DS", DIRECTORY_SEPARATOR);
#redis key 分隔符
define("RKS", ':');


$application = new \Yaf\Application(ROOT_PATH . "/conf/application.ini");

$application->bootstrap()->run();
?>