<?php
/**
 * @throws Custom_Exception|Custom_Exception_404
 * @property Zend_Controller_Request_Http _request
 * @property Custom_View                  view
 * @method Zend_Controller_Request_Http getRequest()
 */
class Custom_Controller_Action_Api extends Zend_Controller_Action {


	const INTERNAL = "INTERNAL";
	const ANY = "ANY";

	/**
	 * @var Zend_Controller_Action_Helper_FlashMessenger
	 */
	protected $_msg = null;
	/**
	 * @var array
	 */
	protected $_requestMethodMap = array();


	public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {

		parent::__construct($request, $response, $invokeArgs);

		/** @var Zend_Controller_Action_Helper_ViewRenderer $viewRenderer */
		$viewRenderer = $this->getHelper('ViewRenderer');
		if ($viewRenderer instanceof Zend_Controller_Action_Helper_ViewRenderer) {
			$viewRenderer->setNoRender(true);
		}

		$this->_msg = $this->getHelper('FlashMessenger');


		$this->_securityCheck();
	}


	public function postDispatch() {


		$this->view->getVars();

		$response = new Model_Api_Response_Entity();
		$response->data = $this->view->getVars();
		$response->messages = $this->_msg->getCurrentMessages();
		$response->ok = !$this->_msg->isError() && !$this->_msg->isWarning();

		$this->_msg->clearCurrentMessages();

		$this->getResponse()->setHeader('Content-Type', 'application/json')
			->setBody(Zend_Json::encode($response->toArray()));
	}


	protected function _securityCheck() {

		$request = $this->getRequest();

		$actionName = $request->getActionName();
		if (!array_key_exists($actionName, $this->_requestMethodMap)) {
			if (DEBUG) {
				throw new Custom_Exception_404('Request method for the action is not defined');
			} else {
				throw new Custom_Exception_404();
			}
		}

		$requestMethodPermission = $this->_requestMethodMap[$actionName];
		if (!is_array($requestMethodPermission)) {
			$requestMethodPermission = array($requestMethodPermission);
		}

		if (in_array(self::INTERNAL, $requestMethodPermission) && !in_array($request->getClientIp(), Zend_Registry::get('config')->api->internal->toArray())) {
			if (DEBUG) {
				throw new Custom_Exception_404('Only internal request is allowed');
			} else {
				throw new Custom_Exception_404();
			}
		}

		if (!in_array(self::ANY, $requestMethodPermission) && !in_array($request->getMethod(), $requestMethodPermission)) {
			if (DEBUG) {
				throw new Custom_Exception_404('Incorrect request method');
			} else {
				throw new Custom_Exception_404();
			}
		}

	}


	protected function _getPluginsData(Zend_Controller_Request_Http $request) {

		$pluginsData = array();
		$sort = $request->getParam(Core_Model::SORT);
		if (!empty($sort)) {
			$pluginsData[Core_Model::SORT] = Core_Request_Plugin_Sort::data($sort);
		}

		$filter = $request->getParam(Core_Model::FILTER);
		if (!empty($filter)) {
			$pluginsData[Core_Model::FILTER] = Core_Request_Plugin_Filter::data($filter);
		}

		$pager = $request->getParam(Core_Model::PAGER);
		if (!empty($pager)) {
			$pluginsData[Core_Model::PAGER] = Core_Request_Plugin_Pager::data($pager);
		} else {
			$limit = $request->getParam(Core_Model::LIMIT);
			if (!empty($limit) && $limit < 50) {
				$pluginsData[Core_Model::LIMIT] = Core_Request_Plugin_Limit::data($limit);
			}
		}

		if (!isset($pluginsData[Core_Model::LIMIT]) && !isset($pluginsData[Core_Model::PAGER])) {
			$pluginsData[Core_Model::LIMIT] = Core_Request_Plugin_Limit::data(50);
		}

		return $pluginsData;
	}


}

