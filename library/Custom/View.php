<?php
/**
 * @method string|Zend_View_Helper_Partial partial()
 * @method string|Zend_View_Helper_HeadLink headLink()
 * @method string|Zend_View_Helper_HeadScript headScript()
 * @method string|Zend_View_Helper_HeadTitle headTitle()
 * @method string|Zend_View_Helper_Translate translate()
 * @method string|Zend_View_Helper_Url url() url(array $urlOptions = array(), $name = null, $reset = false, $encode = true)
 *
 * @method string|Zend_View_Helper_FormHidden formHidden($name, $value = null, array $attribs = null)
 */
class Custom_View extends Zend_View
{


    /**
     * @var array Registry of helper classes used
     */
    protected $_helpersCache = array();


    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return string|mixed
     */
    public function __call($name, $arguments)
    {

        if (method_exists($this, $name)) {
            $result = call_user_func_array(array($this, $name), $arguments);
        } else {
            $helper = $this->getHelper($name);
            $result = call_user_func_array(array($helper, $name), $arguments);
        }

        return $result;
    }


    /**
     * @param string $name
     *
     * @return Zend_View_Helper_Abstract
     */
    public function getHelper($name)
    {

        if (isset($this->_helpersCache[$name]) && ($this->_helpersCache[$name] instanceof Zend_View_Helper_Abstract)) {
            $helper = $this->_helpersCache[$name];
        } else {
            /** @var Zend_View_Helper_Abstract $helper */
            if (Autoloader::getInstance()->autoload($className = 'Custom_View_Helper_' . ucfirst($name))) {
                $helper = new $className();
            } elseif (Autoloader::getInstance()->autoload($className = 'Zend_View_Helper_' . ucfirst($name))) {
                $helper = new $className();
            } elseif ($helper = parent::getHelper($name)) {
            } else {
                Custom_Log::dbLog(
                    'View helper not found', Custom_Log::APP,
                    array('name' => $name)
                );
                throw new Custom_Exception_Debug(Zend_Json::encode(
                    array(
                        'View helper not found',
                        $name,
                    )
                ));
            }

            if (method_exists($helper, 'setView')) {
                $helper->setView($this);
            }
            $this->_helpersCache[$name] = $helper;
        }

        return $helper;
    }


    /**
     *
     */
    public function webRoot()
    {

        /** @var Zend_Controller_Request_Abstract $request */
        $request = Zend_Controller_Front::getInstance()->getRequest();
        if ($request) {
            $schema = $request->getScheme();
        } else {
            $schema = 'http'; // for cron
        }
        $languageEntity = $this->currentLanguage();
        if ($languageEntity->ident == 'en') {
            $webRoot = $schema . "://" . Zend_Registry::get('config')->domain;
        } else {
            $webRoot = $schema . "://$languageEntity->ident." . Zend_Registry::get('config')->domain;
        }

        return $webRoot;
    }


    /**
     * @param string $module
     *
     * @return bool|string
     */
    public function currentModule($module = null)
    {

        $isEqualTo = func_get_args();

        if (empty($isEqualTo)) {
            return Zend_Controller_Front::getInstance()
                ->getRequest()
                ->getModuleName();
        } else {
            return in_array(
                Zend_Controller_Front::getInstance()
                    ->getRequest()
                    ->getModuleName(), $isEqualTo
            );
        }
    }


    /**
     * @param string $controller
     *
     * @return bool|string
     */
    public function currentController($controller = null)
    {

        $isEqualTo = func_get_args();

        if (empty($isEqualTo)) {
            return Zend_Controller_Front::getInstance()
                ->getRequest()
                ->getControllerName();
        } else {
            return in_array(
                Zend_Controller_Front::getInstance()
                    ->getRequest()
                    ->getControllerName(), $isEqualTo
            );
        }
    }


    /**
     * @param string $action
     *
     * @return bool|string
     */
    public function currentAction($action = null)
    {

        $isEqualTo = func_get_args();

        if (empty($isEqualTo)) {
            return Zend_Controller_Front::getInstance()
                ->getRequest()
                ->getActionName();
        } else {
            return in_array(
                Zend_Controller_Front::getInstance()
                    ->getRequest()
                    ->getActionName(), $isEqualTo
            );
        }
    }


    /**
     * @param string $pageIdent
     *
     * @return bool|string
     */
    public function currentPageIdent($pageIdent = null)
    {

        $isEqualTo = func_get_args();

        $request = Zend_Controller_Front::getInstance()
            ->getRequest();
        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();

        $pageIdent = "$module/$controller/$action";

        if (empty($isEqualTo)) {
            return $pageIdent;
        } else {
            return in_array($pageIdent, $isEqualTo);
        }
    }


    /**
     * @param string|array $key
     * @param mixed|null   $value
     *
     * @return string
     */
    public function jsConfig($key, $value = null)
    {

        $configData = array();
        if (is_scalar($key)) {
            $configData[$key] = $value;
        } elseif (is_array($key)) {
            $configData = $key;
        }
        if (empty($configData)) {
            return '';
        }

        return '<div class="jsConfig">' . $this->escape(Zend_Json::encode($configData)) . '</div>';
    }
}