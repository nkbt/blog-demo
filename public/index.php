<?php

require_once './defines.php';
require_once LIB_ROOT . '/Autoloader.php';
require_once LIB_ROOT . '/Debug.php';
$autoloader = Autoloader::getInstance();
$autoloader->load('Custom_Application');
$application = new Custom_Application(APP_ENV);

$application->bootstrap();
$application->run();
if (DEBUG && strstr(Zend_Controller_Front::getInstance()->getResponse()->getBody(), '<!-- DEBUG_BAR -->') !== false) {
	$view = Zend_Layout::getMvcInstance()->getView();
	echo $view->partial('debug/debug.phtml', array('timeStart' => $timeStart));
}