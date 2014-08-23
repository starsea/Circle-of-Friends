<?php
/**
 * @file
 *
 * @notice Redis is required.
 * @see http://pecl.php.net/package/redis
 */
namespace Local\Cache;

use Redis;

class RedisClient extends Redis
{
    public function quit()
    {
        $this->close();
    }
}