<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{


    protected function _initErrorHandler()
    {

        set_error_handler(array('Custom_Log', 'logError'), E_ALL);
        register_shutdown_function(array('Custom_Log', 'shutDown'));
    }


    protected function _initStorage()
    {

        $this->bootstrap('db');
        /** @var Custom_Db_Adapter_Pdo_Mysql $dbAdapter */
        $dbAdapter = $this->getResource('db');
        Zend_Registry::set('dbAdapter', $dbAdapter);
    }


    protected function _initViewResource()
    {

        $this->bootstrap('view');
        $view = $this->getResource('view');

        $view->headTitle()
            ->setSeparator(' - ')
            ->setDefaultAttachOrder(Zend_View_Helper_Placeholder_Container_Abstract::PREPEND);
        $view->headLink(array('rel' => 'shortcut icon', 'href' => Zend_Registry::get('config')->favicon, 'type' => 'image/x-icon'));


        $navigationConfig = Zend_Registry::get('navigationConfig');

        $navigation = new Zend_Navigation($navigationConfig);
        $view->navigation()->setContainer($navigation);

    }
}

