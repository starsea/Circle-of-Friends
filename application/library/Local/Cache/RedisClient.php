<?php
/**
 * @file
 *
 * @notice Redis is required.
 * @see http://pecl.php.net/package/redis
 *
 */

namespace Local\Cache;

use Redis;


class RedisClient extends Redis
{
    private $_linkHandle = array();


    public function addServer(Array $config = array('host' => '127.0.0.1', 'port' => 6379), $name = '')
    {
        empty($name) && $name = $config;
        is_array($name) && $name = $this->arr2String($name);


        $this->_linkHandle[$name] = new Redis();

        if (!$ret = $this->_linkHandle[$name]->connect($config['host'], $config['port'])) {

            throw new \RedisException('Redis server ' . $this->arr2String($config) . ' 链接失败');
        }

        return $ret;
    }

    public function addServers(Array $servers)
    {
        foreach ($servers as $v) {
            $this->addServer($v[0], $v[1]);
        }
    }

    /**
     * @desc 设置当前 link
     * @return $this
     */
    public function getInstance($config)
    {
        $name = $config; //string

        if (is_array($config)) { // 配置文件
            $name = $this->arr2String($config);
        }

        if (!in_array($name, $this->_linkHandle)) {
            $this->addServer($config);
        }

        $this->socket = $this->_linkHandle[$name]->socket;

        return $this;
    }

    /**
     * @desc 返回当前 redis handle
     *
     * @return resource
     */
    public function getCurrRedis()
    {
        return $this->socket;
    }

    public function quit()
    {
        $this->close();
    }


    /**
     * @desc  弹出最小的 member 和 score 的关联数组
     * @return array
     */
    public function zPopMin($key, $withscore = true)
    {
        $this->multi();
        $this->ZRANGE($key, 0, 0, $withscore);
        $this->zRemRangeByRank($key, 0, 0); // 从小到大排序  移除最小的为顶部
        $ret = $this->exec();

        if (empty($ret[0])) {
            return false; // 不存在集合
        }
        return $ret[0];

    }

    /**
     * @desc  弹出最大的 member 和 score 的关联数组
     * @return array
     */
    public function zPopMax($key, $withscore = true)
    {
        $this->multi();
        $this->ZRANGE($key, -1, -1, $withscore);
        $this->zRemRangeByRank($key, -1, -1); // 从小到大排序  移除最小的为顶部
        $ret = $this->exec();

        return $ret[0];
    }

    public function arr2String(Array $arr)
    {
        $arr = implode('@', $arr);
        return $arr;
    }
}