<?php

/**
 * @throws Custom_Exception|Custom_Exception_404
 * @property Zend_Controller_Request_Http _request
 * @property Custom_View                  view
 * @method Zend_Controller_Request_Http getRequest()
 */
class ApiController extends Custom_Controller_Action
{


    public function init()
    {

        $this->_isAjax = true;
        $this->setParam('ajax', true);
    }


    public function eventAction()
    {

        Zend_Registry::get('Redis')->lPush('Log:PHPEventController', Zend_Json::encode($this->getAllParams()));

        $event = $this->getParam('event');
        $target = $this->getParam('target');
        $source = $this->getParam('source');

        if (empty($event)) {
            throw new Custom_Exception_404("Event not defined");
        }
        
        if (empty($target)) {
            throw new Custom_Exception_404("Target entity not defined");
        }

        if (empty($source)) {
            throw new Custom_Exception_404("Event source entity not defined");
        }

        $model = Core_Model_Factory::get($target);

        $method = $event . str_replace('_', '', $source);
        if (!method_exists($model, $method)) {
            $class = get_class($model);
            throw new Custom_Exception_404("Method $method is not implemented in $class");
        }
        try {
            set_error_handler(array($this, '_errorHandler'));
            $data = unserialize(base64_decode($this->getParam('data')));
            Zend_Registry::get('Redis')->lPush('Log:PHPEventControllerData', Zend_Json::encode($data));
            restore_error_handler();

            call_user_func(array($model, $method), $data);
        } catch (Custom_Exception $ex) {
            Custom_Log::dbLog("Invalid input", Custom_Log::APP, $this->getAllParams());
            throw $ex;
        }
    }


    public function testAction()
    {

        /** @var Model_User_Entity $currentUser */
        $currentUser = Zend_Auth::getInstance()->getIdentity();

        $this->view->assign('params', $this->getAllParams());
        $this->view->assign('user', $currentUser);
    }


    protected function _errorHandler()
    {

        throw new Custom_Exception("Invalid input");
    }

}