<?php
namespace Utility;

class Alias
{
    public static function redis($target = 'default')
    {
        return \Local\Cache\RedisManager::getConnection($target);
    }

    public static function  microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

}