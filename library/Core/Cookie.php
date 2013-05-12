<?php
class Core_Cookie
{


    const COOKIE_YEAR = 31536000; # 365 days
    const COOKIE_MONTH = 2592000; # 30 days
    const COOKIE_WEEK = 604800; # 7 days
    const COOKIE_DAY = 86400; # 1 days
    const COOKIE_MINUTE = 60;
    const COOKIE_HOUR = 3600;


    public static function get($name = null, $default = null)
    {

        if (null === $name) {
            return $_COOKIE;
        }

        return (isset($_COOKIE[$name])) ? $_COOKIE[$name] : $default;
    }


    /**
     * @static
     *
     * @param string      $name
     * @param null|string $value
     * @param null|int    $expire Seconds to live (always added to time()), to delete cookie use delete() method
     * @param null|string $path
     * @param null|string $domain
     * @param null|string $secure
     * @param null|string $httponly
     *
     * @throws Custom_Exception
     * @return string $value
     */
    public static function set($name, $value = null, $expire = 0, $path = '/', $domain = null, $secure = null, $httponly = null)
    {

        $time = time();
        if ($expire < 0) {
            throw new Custom_Exception("Don't use negative expire time for a cookie. To delete a cookie use delete() method");
        }
        if ($expire !== 0 && $expire < $time) {
            $expire = $expire + $time;
        }
        setcookie($name, $value, $expire, $path, self::decorateCookieDomain($domain), $secure, $httponly);
        $_COOKIE[$name] = $value;

        return $value;
    }


    public static function has($name)
    {

        return isset($_COOKIE[$name]);
    }


    public static function delete($name, $path = '/', $domain = null, $secure = null, $httponly = null)
    {

        setcookie($name, false, time() - 172800, $path, self::decorateCookieDomain($domain), $secure, $httponly);
        unset($_COOKIE[$name]);

        return null;
    }


    public static function decorateCookieDomain($domain = null)
    {

        $cookieDomain = ini_get('session.cookie_domain');
        if (!empty($cookieDomain)) {
            $domain = $cookieDomain;
        }

        return $domain;
    }

}