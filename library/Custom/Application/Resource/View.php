<?php

class Custom_Application_Resource_View extends Zend_Application_Resource_ResourceAbstract {


	/**
	 * @var Zend_View_Interface
	 */
	protected $_view;


	/**
	 * Defined by Zend_Application_Resource_Resource
	 *
	 * @return Zend_View
	 */
	public function init() {
		$view = $this->getView();
		$viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
		$viewRenderer->setView($view);
		Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
		return $view;
	}


	/**
	 * Retrieve view object
	 *
	 * @return Zend_View
	 */
	public function getView() {
		if (null === $this->_view) {
			$options = $this->getOptions();
			$this->_view = new Custom_View($options);

			if (isset($options['doctype'])) {
				$this->_view->doctype()->setDoctype(strtoupper($options['doctype']));
				if (isset($options['charset']) && $this->_view->doctype()->isHtml5()) {
					$this->_view->headMeta()->setCharset($options['charset']);
				}
			}
			if (isset($options['contentType'])) {
				$this->_view->headMeta()->appendHttpEquiv('Content-Type', $options['contentType']);
			}
		}
		return $this->_view;
	}
}
