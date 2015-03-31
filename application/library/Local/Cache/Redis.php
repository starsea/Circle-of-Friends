<?php
/**
 * @file
 *
 * @desc 扩展 原生 redis用法 加入 zpop 等功能
 * @notice Redis is required.
 * @see http://pecl.php.net/package/redis
 *
 */

namespace Local\Cache;

use Redis as RD;


class Redis extends RD
{


    public function setX($namespace, $key, $timeout = 0.0)
    {
        $this->lPush($namespace, $key);
        return parent::set($key, $timeout);

    }


    public function quit()
    {
        $this->close();
    }


    public function lPushArr($key, Array $arr)
    {
        array_unshift($arr, $key);

        return call_user_func_array(array($this, 'lPush'), $arr);

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

    /**
     * @param $key string
     * @param $value array
     * @param $length int
     * @desc sorted set 构成的有限长度的队列 采用 LRU 算法 最近访问也就是最新的再顶部
     *
     */
    public function zAddQueue($zKey, Array $v, $length)
    {
        $member = $v[0];
        $value  = json_encode($v[1]);

//        $count = $this->zCard($zKey);

        $this->set($member, $value);
        $this->zAdd($zKey, time(), $member); // 根据队列判断时候存在比较合理


        $count = $this->zCard($zKey);

        if ($count > $length) {

            $willDeleteKey = $this->zPopMin($zKey, false); // multi  //must not nested
            $this->delete($willDeleteKey);

//            $willDeleteKey = $this->zRange($zKey, $length, -1);
//            $this->zRemRangeByRank($zKey, $length, -1); // 最大的被干掉了 不科学
//            $this->delete($willDeleteKey);

        }

    }

    /**
     * @param $zKey
     * @param array $v
     * @param $length
     *
     * @desc 基于乐观锁的缓冲队列
     */

    public function test($zKey, Array $v, $length)
    {
        $member = $v[0];
        $value  = json_encode($v[1]);

        $i = 0;
        do {
            $i++;
            $this->watch($zKey);

            $count = $this->zCard($zKey);

            $this->multi();

            $this->set($member, $value);
            $this->zAdd($zKey, $member, $member); // 根据队列判断时候存在比较合理

            if ($count > $length) { // 101+1
                $this->ZRANGE($zKey, 0, 1);
                $this->zRemRangeByRank($zKey, 0, 1); // 从小到大排序  移除最小的为顶部
            }
        } while (!($ret = $this->exec()));


        var_dump($ret, $i);

        if (isset($ret[2]) && is_array($ret[2])) {
            $this->delete($ret[2]);
        }


    }

    /**
     * @param string $key
     * @param null $option
     * @param bool $format
     * @return array
     */
    public function sort($key, $option = null, $format = true)
    {

        $origin = parent::sort($key, $option);

        if ($format && is_array($origin) && !empty($origin)) {
            if (isset($option['key']) && is_array($option['key'])) {

                $count = count($option['key']);

                while ($output = array_splice($origin, 0, $count)) {

                    $new[] = array_combine($option['key'], $output);
                }

                return $new;

            }
        }

        return $origin;
    }


}