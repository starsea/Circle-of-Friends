<?php
/**
 * Created by PhpStorm.
 * User: haidx
 * Date: 14-10-22
 * Time: 16:49
 */

namespace Utility;


class Validator
{

    //一般用来检察变量数组中是否有空值
    public static function  isEmpty($var)
    {
        if (!is_array($var)) {
            $var = array($var);
        }
        foreach ($var as $v) {
            if (empty($v)) {
                return true;
            }
        }
        return false;
    }
} 