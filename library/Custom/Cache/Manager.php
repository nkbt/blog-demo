<?php
class Custom_Cache_Manager extends Zend_Cache_Manager
{


    /**
     * @var Custom_Cache_Manager
     */
    protected static $_instance;
    /**
     * @var array
     */
    protected $_defaultTemplate = array(
        'frontend' => array(
            'name' => 'Core',
            'options' => array(
                'lifetime' => 7200,
                'automatic_serialization' => true,
                'caching' => CACHE_ENABLED,
                'automatic_cleaning_mode' => 0,
            ),
        ),
        'backend' => array(
            'name' => 'Custom_Cache_Backend_Redis',
            'customBackendNaming' => true,
            'options' => array(
                'registry' => 'Redis',
            ),
        ),
        'frontendBackendAutoload' => true,
        'logger' => false,
    );
    /**
     * @var Zend_Log
     */
    protected $_logger;


    protected function __construct()
    {

        $config = (include APP_ROOT . '/configs/cache.php');
        foreach ($config as $template => $options) {
            if (!empty($options['frontend']['options']['logging']) && empty($options['frontend']['options']['logger'])) {
                $options['frontend']['options']['logger'] = $this->_getLogger();
            }
            $this->setCacheTemplate($template, $this->_decorateDefaultTemplate($options));
        }
    }


    /**
     * @static
     * @return Custom_Cache_Manager
     */
    public static function getInstance()
    {

        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }


    /**
     * @return Zend_Log
     */
    protected function _getLogger()
    {

        if (!$this->_logger) {
            $this->_logger = new Zend_Log();
            $this->_logger->addWriter(new Zend_Log_Writer_Stream(APP_ROOT . '/caches/log.txt'));
        }

        return $this->_logger;
    }


    /**
     * @param array $options
     *
     * @return array
     */
    protected function _decorateDefaultTemplate($options)
    {

        return $this->_mergeOptions($this->_defaultTemplate, $options);
    }


    private function __clone()
    {
    }


    private function __wakeup()
    {
    }
}
