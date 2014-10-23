<?php
namespace Utility;

class Alias
{
    public static function redis($target = 'default')
    {
        return \Local\Cache\RedisManager::getConnection($target);
    }
}