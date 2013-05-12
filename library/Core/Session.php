<?php
class Core_Session
{


    protected static $_namespaceCache = array();


    /**
     * @static
     *
     * @param string      $namespace
     * @param null|string $key
     * @param null|mixed  $default
     *
     * @return mixed|Zend_Session_Namespace
     */
    public static function get($namespace, $key = null, $default = null)
    {

        self::_init($namespace);
        if (is_null($key)) {
            $result = self::$_namespaceCache[$namespace];
        } else {
            $result = isset(self::$_namespaceCache[$namespace]->{$key}) ? self::$_namespaceCache[$namespace]->{$key} : $default;
        }

        return $result;
    }


    /**
     * @static
     *
     * @param string     $namespace
     * @param string     $key
     * @param null|mixed $value
     *
     * @return mixed|null
     */
    public static function set($namespace, $key, $value)
    {

        self::_init($namespace);
        self::$_namespaceCache[$namespace]->{$key} = $value;

        return $value;
    }


    /**
     * @static
     *
     * @param string $namespace
     * @param string $key
     *
     * @return bool
     */
    public static function has($namespace, $key)
    {

        self::_init($namespace);

        return isset(self::$_namespaceCache[$namespace]->{$key});
    }


    /**
     * @static
     *
     * @param string      $namespace
     * @param null|string $key
     *
     * @return null
     */
    public static function delete($namespace, $key = null)
    {

        self::_init($namespace);
        if (is_null($key)) {
            self::$_namespaceCache[$namespace]->unsetAll();
        } else {
            unset(self::$_namespaceCache[$namespace]->{$key});
        }

        return null;
    }


    protected static function _init($namespace)
    {

        if (!isset(self::$_namespaceCache[$namespace])) {
            self::$_namespaceCache[$namespace] = new Zend_Session_Namespace($namespace, true);
        }

        return self::$_namespaceCache[$namespace];
    }
}