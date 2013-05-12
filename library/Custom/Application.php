<?php

class Custom_Application extends Zend_Application
{


    public function __construct($environment, $options = null)
    {

        $this->_environment = (string)$environment;

        $this->_autoloader = Autoloader::getInstance();

        if (null !== $options) {
            if (is_string($options) && is_readable($options)) {
                $options = $this->getConfig('application', $options)->{$this->_environment}->toArray();
            } elseif ($options instanceof Zend_Config) {
                $options = $options->toArray();
            } elseif (!is_array($options)) {
                throw new Zend_Application_Exception('Invalid options provided; must be location of config file, a config object, or an array');
            }
            if (isset($options['bootstrap']['path'])) {
                require_once $options['bootstrap']['path'];
            }
            if (DEBUG) {
                $options['resources']['db']['params']['profiler']['enabled'] = 1;
            }

            $this->setOptions($options);
        }

        $this->_initRedis();
        $this->_initConfigs();
    }


    public function getConfig($name, $file)
    {

        $cache = Custom_Cache_Manager::getInstance()->getCache('config');

        $mtime = filemtime($file);

        $cacheId = "config__{$name}__file";
        $cacheMtimeId = "config__{$name}__mtime";

        $configMtime = $cache->load($cacheMtimeId);
        $config = $configMtime === $mtime ? $cache->load($cacheId) : false;

        if ($config !== false) {
            return $config;
        }

        $suffix = pathinfo($file, PATHINFO_EXTENSION);
        $suffix = ($suffix === 'dist') ? pathinfo(basename($file, ".$suffix"), PATHINFO_EXTENSION) : $suffix;

        switch (strtolower($suffix)) {
            case 'ini':
                $config = new Zend_Config_Ini($file);
                break;

            case 'xml':
                $config = new Zend_Config_Xml($file);
                break;

            case 'json':
                $config = new Zend_Config_Json($file);
                break;

            case 'yaml':
            case 'yml':
                $config = new Zend_Config_Yaml($file);
                break;

            case 'php':
            case 'inc':
                $configArray = include $file;
                if (!is_array($configArray)) {
                    throw new Zend_Application_Exception('Invalid configuration file provided; PHP file does not return array value');
                }
                $config = new Zend_Config($configArray);
                break;

            default:
                throw new Zend_Application_Exception('Invalid configuration file provided; unknown config type');
        }

        $cache->save($config, $cacheId);
        $cache->save($mtime, $cacheMtimeId);

        return $config;
    }


    protected function _initRedis()
    {

        $config = (include APP_ROOT . '/configs/redis.php');
        $envConfig = isset($config[$this->getEnvironment()]) ? $config[$this->getEnvironment()] : array();
        $server = isset($envConfig['server']) ? $envConfig['server'] : $config['redis']['server'];
        $port = isset($envConfig['port']) ? $envConfig['port'] : $config['redis']['port'];
        $timeout = isset($envConfig['timeout']) ? $envConfig['timeout'] : $config['redis']['timeout'];

        $redis = new Redis();
        $redis->connect($server, $port, floatval($timeout));
        Zend_Registry::set('Redis', $redis);
    }


    protected function _initConfigs()
    {

        $configs = array(
            'applicationConfig' => 'application.ini',
            'fileConfig' => 'files.ini',
            'navigationConfig' => 'navigation.xml',
        );

        foreach ($configs as $registryName => $fileName) {
            Zend_Registry::set($registryName, $this->getConfig($registryName, APP_ROOT . "/configs/$fileName"));
        }

        /** @var Zend_Config $applicationConfig */
        $applicationConfig = Zend_Registry::get('applicationConfig')->{$this->getEnvironment()};
        $customConfig = $applicationConfig->customConfig;

        defined('DEBUG') || define('DEBUG', (bool)$customConfig->debugMode);

        $options = $applicationConfig->toArray();
        if (DEBUG) {
            $options['resources']['db']['params']['profiler']['enabled'] = 1;
        }

        $this->setOptions($options);

        Zend_Registry::set('config', $customConfig);

        Zend_Registry::set('redirectCounter', $customConfig->redirectCounter);
        define('NO_CAPTCHA', (bool)$customConfig->noCaptcha);
        define('UPLOAD_ROOT', Zend_Registry::get('fileConfig')->tmp->uploadDir);
    }

}
