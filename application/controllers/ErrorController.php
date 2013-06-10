<?php

class ErrorController extends Custom_Controller_Action
{


    public function errorAction()
    {

        $errorHandler = $this->_getParam('error_handler');

        if (!$errorHandler || !$errorHandler instanceof ArrayObject) {
            $this->view->message = 'You have reached the error page';

            return;
        }

        if ($errorHandler->request->getParam('ajax')) {
            $this->_isAjax = true;
            $this->_ajaxError($errorHandler);
        } else {
            $this->_error($errorHandler);
        }

        if ($errorHandler->exception instanceof Exception) {
            Custom_Log::dbLog($errorHandler->exception);
        }

    }


    protected function _ajaxError($errorHandler)
    {


        /** @var Exception $exc */
        $exc = $errorHandler->exception;

        switch ($errorHandler->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:

                $this->getResponse()->setHttpResponseCode(501);
                if (DEBUG) {
                    $this->_msg->addMessage($exc->getMessage());
                } else {
                    $this->_msg->addMessage('Not implemented');
                }
                break;

            default:
                if ($exc instanceof Custom_Exception_404) {

                    $this->getResponse()->setHttpResponseCode(501);
                    if (DEBUG) {
                        $this->_msg->addMessage($exc->getMessage());
                    } else {
                        $this->_msg->addMessage('Not implemented');
                    }

                } else {

                    $this->getResponse()->setHttpResponseCode(500);
                    if (DEBUG) {
                        $this->_msg->addMessage($exc->getMessage());
                    } else {
                        $this->_msg->addMessage('Application error');
                    }

                }
                break;
        }

        $this->view->assign('request', $errorHandler->request->getParams());
    }


    protected function _error($errorHandler)
    {

        $priority = Zend_Log::ERR;
        
        /** @var Exception $exc */
        $exc = $errorHandler->exception;

        switch ($errorHandler->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Page not found';
                break;
            default:
                if ($exc instanceof Custom_Exception_404) {
                    $this->getResponse()->setHttpResponseCode(404);
                    $this->view->message = 'Page not found';
                } else {
                    $this->getResponse()->setHttpResponseCode(500);
                    $this->view->message = 'Application error';
                }
                $priority = Zend_Log::CRIT;
                break;
        }


        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errorHandler->exception;
        }

        $this->view->request = $errorHandler->request;

        return $priority;
    }


}

