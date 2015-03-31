<?php
namespace Utility;

/**
 * Class     Cookie
 * Cookie工具类
 *
 * @package  Vendor
 * @author   luoliang1
 */
class Cookie
{

    /**
     * Method  _decrypt
     * 解密cookie
     *
     * @author luoliang1
     * @static
     *
     * @param $encrypted_text
     *
     * @return string
     */
    private static function _decrypt($encrypted_text)
    {
        $key          = \Yaf\Registry::get('config')->cookie->secret_key;
        $crypt_text   = base64_decode($encrypted_text);
        $ivSize       = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv           = mcrypt_create_iv($ivSize, MCRYPT_RAND);
        $decrypt_text = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $crypt_text, MCRYPT_MODE_ECB, $iv);

        return trim($decrypt_text);
    }

    /**
     * Method  _encrypt
     * 加密cookie
     *
     * @author luoliang1
     * @static
     *
     * @param $plain_text
     *
     * @return string
     */
    private static function _encrypt($plain_text)
    {
        $key          = \Yaf\Registry::get('config')->cookie->secret_key;
        $iv_size      = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv           = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $encrypt_text = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $plain_text, MCRYPT_MODE_ECB, $iv);

        return trim(base64_encode($encrypt_text));
    }

    /**
     * Method  delete
     * 删除cookie
     *
     * @author luoliang1
     * @static
     *
     * @param      $name
     * @param null $domain
     *
     * @return bool
     */
    public static function delete($name, $domain = null)
    {
        return isset($_COOKIE[$name]) ? setcookie($name, '', time() - 86400, '/', $domain) : true;
    }

    /**
     * Method  get
     * 获取cookie
     *
     * @author luoliang1
     * @static
     *
     * @param $name
     *
     * @return null|string
     */
    public static function get($name)
    {
        return isset($_COOKIE[$name]) ? self::_decrypt($_COOKIE[$name]) : null;
    }

    /**
     * Method  set
     * 设置cookie
     *
     * @author luoliang1
     * @static
     *
     * @param        $name
     * @param        $value
     * @param null $expire
     * @param string $path
     * @param null $domain
     * @param int $secure
     *
     * @return bool
     */
    public static function set($name, $value, $expire = null, $path = '/', $domain = null, $secure = 0)
    {
        $value = self::_encrypt($value);

        if (null == $expire) {
            $expire = \Yaf\Registry::get('config')->cookie->expire;
        }
        $expire = time() + (int)$expire;

        return setcookie($name, $value, $expire, $path, $domain, $secure);
    }
}