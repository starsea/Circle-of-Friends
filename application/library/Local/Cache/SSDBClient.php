<?php

namespace Local\Cache;
/**
 * All methods(except *exists) returns false on error,
 * so one should use Identical(if($ret === false)) to test the return value.
 */
class SSDBClient
{
    /**
     * 链接
     *
     * @var array
     */
    protected static $connections = array();

    /**
     * 配置
     *
     * @var array
     */
    protected static $redisInfo = array();

    /**
     * 是否已经手工设置过配置
     *
     * @var bool
     */
    protected static $isConfiguration = false;

    //设置连接信息
    final public static function setConfig($target, array $config = array())
    {
        if (is_string($target)) {
            self::$redisInfo[$target] = $config;
        } elseif (is_array($target)) {
            self::$redisInfo = $target + self::$redisInfo; //
        }
        self::$isConfiguration = true;
    }


    /**
     * @desc 获取链接
     * @param string $target
     * @return SSDB resource
     */
    final public static function getConnection($target = 'default')
    {
        if (!isset(self::$connections[$target])) {
            self::$connections[$target] = self::openConnection($target);
        }

        return self::$connections[$target];
    }

    //打开链接
    final public static function openConnection($target)
    {
//        if (empty(self::$redisInfo)) {
//            self::parseConnectionInfo();
//        }

        $connection = new SSDB();
        if (isset(self::$redisInfo[$target])) {
            $info = self::$redisInfo[$target];

        } else {
            $info = array(
                'host'     => '127.0.0.1',
                'port'     => 8888,
                'timeout'  => 2000,
                'database' => 0,
                'password' => '',
                'other'    => 'default', // not use
//                'options'  => array(),
            );
        }
        try {
            $info['timeout'] = isset($info['timeout']) ? $info['timeout'] : 0;

            $connection->connect($info['host'], $info['port'], $info['timeout']);

            if (!empty($info['password'])) { // 禁止设置密码为0
                $connection->auth($info['password']);
            }
//             ssdb 没有数据库的概念
//            if (isset($info['database'])) {
//                $connection->select($info['database']);
//            }

//            if (!empty($info['options'])) {
//                foreach ($info['options'] as $k => $v) {
//                    $connection->setOption($k, $v);
//                }
//            }
            if ($info['easy']) {
                $connection->easy();
            };

        } catch (\Exception $e) {
            $connection = null;
            throw $e;
        }

        return $connection;
    }

    //关闭链接
    public static function closeConnection($target = null)
    {
        if (isset($target)) {
            if (isset(self::$connections[$target])) {
                self::$connections[$target]->close();
                self::$connections[$target] = null;
                unset(self::$connections[$target]);
            }
        } else {
            foreach (self::$connections as $target => $connection) {
                self::$connections[$target]->close();
                self::$connections[$target] = null;
                unset(self::$connections[$target]);
            }
        }
    }

    //解析配置信息
    final public static function parseConnectionInfo(Array $redises)
    {
//        global $redises;

        if (!self::$isConfiguration) {
            $redisInfo       = is_array($redises) ? $redises : array();
            self::$redisInfo = $redisInfo;
        }
    }

    // test function
    public static function my()
    {
        return self::$connections;
    }


}