<?php

class Autoloader
{


    const AUTOLOADER_CALLBACK = 'autoloader_callback';
    /**
     * @var Autoloader
     */
    protected static $_instance;
    /**
     * @var array Namespace => RootPath
     */
    protected $_namespaces = array();
    /**
     * @var array ClassName => FilePath
     */
    protected $_classMap = array();
    /**
     * @var bool
     */
    protected $_isClassMapChanged = false;


    protected function __construct()
    {

        $this->_namespaces = array(
            'Form' => APP_ROOT . DIRECTORY_SEPARATOR . 'forms',
            'Model' => APP_ROOT . DIRECTORY_SEPARATOR . 'models',
            'Core' => LIB_ROOT,
            'Custom' => LIB_ROOT,
            'Zend' => ZEND_ROOT,
        );
        if (!file_exists(APP_ROOT . '/caches/classMap')) {
            file_put_contents(APP_ROOT . '/caches/classMap', serialize(array()));
        }
        $this->_classMap = unserialize(file_get_contents(APP_ROOT . '/caches/classMap'));
        if (!$this->_classMap) {
            $this->_classMap = array();
        }
        spl_autoload_register(array(__CLASS__, 'autoload'));
    }


    /**
     * @static
     * @return Autoloader
     */
    public static function getInstance()
    {

        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }


    /**
     * @static
     *
     * @param string $className
     *
     * @return bool
     */
    public static function autoload($className)
    {

        return self::getInstance()->load($className);
    }


    public function __destruct()
    {

        if ($this->_isClassMapChanged) {
            file_put_contents(APP_ROOT . '/caches/classMap', serialize($this->_classMap));
        }
    }


    public function getPath($className)
    {

        if (isset($this->_classMap[$className])) {
            return $this->_classMap[$className];
        }

        $parts = explode('_', $className);
        $namespace = $parts[0];
        $filename = str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        if (!isset($this->_namespaces[$namespace])) {
            foreach ($this->_namespaces as $registeredNamespace => $path) {
                if (strstr($className, $registeredNamespace) !== false) {
                    $namespace = $registeredNamespace;
                }
            }
        }

        if (!isset($this->_namespaces[$namespace])) {
            throw new Exception("$className: namespace not set");
        }

        $result = false;

        if ($this->_namespaces[$namespace] == self::AUTOLOADER_CALLBACK) {
            $callbackName = '_' . strtolower($namespace) . 'Callback';
            $result = $this->$callbackName($className);
        } else {
            if (is_array($this->_namespaces[$namespace])) {
                foreach ($this->_namespaces[$namespace] as $root) {
                    $path = $root . DIRECTORY_SEPARATOR . $filename;
                    if (file_exists($path)) {
                        $result = $path;
                    }
                }
            } else {
                $path = $this->_namespaces[$namespace] . DIRECTORY_SEPARATOR . $filename;
                $result = $path;
            }
        }

        if ($result) {
            $this->_classMap[$className] = $result;
            $this->_isClassMapChanged = true;
        }

        return $result;
    }


    /**
     * @static
     *
     * @param string $className
     *
     * @return bool
     */
    public function load($className)
    {

        if (class_exists($className, false) || interface_exists($className, false)) {
            return true;
        }
        $filepath = $this->getPath($className);

        return $filepath ? include_once($filepath) : false;
    }


    private function __clone()
    {
    }


    private function __wakeup()
    {
    }

}