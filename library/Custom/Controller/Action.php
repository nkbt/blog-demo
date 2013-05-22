<?php
/**
 * @throws Custom_Exception|Custom_Exception_404
 *
 * @property Zend_Controller_Request_Http _request
 * @property Custom_View                  view
 *
 * @method Zend_Controller_Request_Http getRequest()
 */
class Custom_Controller_Action extends Zend_Controller_Action
{


    /**
     * @var Zend_Controller_Action_Helper_FlashMessenger
     */
    protected $_msg = null;
    protected $_isAjax;
    protected $_ident = null;


    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {

        /** @var Zend_Controller_Request_Http $request */
        /** @var Zend_Controller_Response_Http $response */
        $this->setRequest($request)
            ->setResponse($response)
            ->_setInvokeArgs($invokeArgs);
        $this->_helper = new Zend_Controller_Action_HelperBroker($this);

        /** @var Zend_Controller_Action_Helper_ViewRenderer $viewRenderer */
        $viewRenderer = $this->getHelper('ViewRenderer');
        $this->_isAjax = $request->isXmlHttpRequest() || !!$request->getParam('ajax');
        $this->_msg = $this->getHelper('FlashMessenger');


        $this->init();

        if ($this->_isAjax) {
            $viewRenderer->setNoRender();
            Zend_Layout::getMvcInstance()->disableLayout();
        }

    }


    public function postDispatch()
    {

        if (!$this->isAjax()) {
            return;
        }
        $response = new Model_Api_Response_Entity();
        $response->data = $this->view->getVars();
        $response->messages = $this->_msg->getCurrentMessages();
        $response->ok = $this->getRequest()->getControllerName() !== 'error';
        $this->_msg->clearCurrentMessages();

        $this->getResponse()->setHeader('Content-Type', 'application/json')
            ->setBody(Zend_Json::encode($response));
    }


    public function isAjax()
    {

        return $this->_isAjax;
    }


}

