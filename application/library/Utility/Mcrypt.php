<?php
/**
 * Mcrypt 加密模块
 *
 * User: eagle
 * Date: 14-8-31
 * Time: 上午1:22
 */

namespace App\Lib;


class Mcrypt
{
    CONST ENCRYPT = 1;
    CONST DECRYPT = 2;

    CONST SECRET_KEY = '630b7a5bc60dbc9b';


    public static function process($type, $data, $key, $iv = '', $settings = array())
    {
        if (empty($data)) {
            return $data;
        }

        //合并默认参数
        $settings += array(
            'algorithm' => MCRYPT_RIJNDAEL_256, //AES256算法
            'mode' => MCRYPT_MODE_CBC //Reference: http://php.net/manual/zh/mcrypt.constants.php
        );

        //打开算法和模式对应的模块
        $module = mcrypt_module_open($settings['algorithm'], '', $settings['mode'], '');

        $iv or $iv = self::getIv(\APP_TS, self::SECRET_KEY);

        //打开的算法的初始向量大小, 在 cbc，cfb 和 ofb 模式以及某些流模式算法中会用到初始向量
        $ivSize = mcrypt_enc_get_iv_size($module);
        if (strlen($iv) > $ivSize) {
            $iv = substr($iv, 0, $ivSize);
        }

        //根据打开模式所能支持的最长密钥长度，截取密钥
        $keySize = mcrypt_enc_get_key_size($module);
        if (strlen($key) > $keySize) {
            $key = substr($key, 0, $keySize);
        }

        mcrypt_generic_init($module, $key, $iv);

        if ($type == self::ENCRYPT) {
            //加密
            $data = mcrypt_generic($module, $data); //@mcrypt_generic($module, $data);
        } else {
            //解密
            $data = mdecrypt_generic($module, $data); //@mdecrypt_generic($module, $data);
            $data = rtrim($data, "\0");
        }

        mcrypt_generic_deinit($module);

        return $data;
    }

    /**
     * 加密数据
     *
     * 使用给定的密钥key和向量加密data数据，默认采用 RIJNDAEL/AES 256 加密
     * 切换算法或者模式，覆盖 settings 参数即可 格式如下：
     * array(
     *      'algorithm' => MCRYPT系列算法算法常量,
     *      'mode' => MCRYPT系列模式常量
     * )
     *
     * @param  string $data 需要加密的数据
     * @param  string $key 密钥
     * @param  string $iv 初始化向量
     * @param  array $settings 加密方式配置
     * @return string
     */
    public static function encrypt($data, $key, $iv = '', $settings = array())
    {
        return self::process(self::ENCRYPT, $data, $key, $iv, $settings);
    }


    /**
     * 解密数据
     *
     * 同 encrypt 默认使用 RIJNDAEL/AES 256 算法
     *
     * @param  string $data 加密数据
     * @param  string $key 密钥
     * @param  string $iv 初始化向量
     * @param  array $settings 解密方式配置
     * @return string
     */
    public static function decrypt($data, $key, $iv = '', $settings = array())
    {
        return self::process(self::DECRYPT, $data, $key, $iv, $settings);
    }


    /**
     * 产生乱序且无法预测的向量值
     *
     * @param  int $expires 时间戳
     * @param  string $secret 密钥
     * @return string Hash
     */
    public static function getIv($expires, $secret)
    {
        $data1 = hash_hmac('sha1', 'a' . $expires . 'b', $secret);
        $data2 = hash_hmac('sha1', 'z' . $expires . 'y', $secret);

        return pack("h*", $data1 . $data2);
    }

} 