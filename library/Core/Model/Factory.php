<?php
/**
 * Created by JetBrains PhpStorm.
 * User: nbutenko
 * Date: 3/01/13
 * Time: 4:31 PM
 * To change this template use File | Settings | File Templates.
 */
class Core_Model_Factory
{


    /**
     * @var Core_Model[]
     */
    protected static $_cache = null;


    protected function __construct()
    {
    }


    protected function __clone()
    {
    }


    /**
     * @param string $name Case sensitive model name, could be full class name like *Model_UserParkingReport*, or short a one *UserParkingReport*
     *
     * @return Core_Model
     */
    public static function get($name)
    {

        $className = strstr($name, 'Model_') === false ? "Model_$name" : $name;

        if (!isset(static::$_cache[$name])) {
            static::$_cache[$name] = new $className;
        }

        return static::$_cache[$name];
    }


}
