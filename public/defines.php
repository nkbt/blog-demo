<?php

$config = array(
	'APP_ENV' => 'development',
	'APP_ROOT' => '/../application',
	'LIB_ROOT' => '/../library',
	'ZEND_ROOT' => '/../library',
	'CACHE_ENABLED' => 0,
	'DEBUG' => 1,
);

$appEnv = getenv('APP_ENV');
if (!$appEnv) {
	$appEnv = $config['APP_ENV'];
}
define('APP_ENV', $appEnv);

/**
 * @global
 */
$env = getenv('APP_ROOT');
if (!$env) {
	$env = $config['APP_ROOT'];
}
$path = realpath($env);
if (!$path) {
	$path = realpath(dirname(__FILE__) . $env);
}
if (!$path) {
	throw new Exception("'APP_ROOT' not found: $env");
}
define('APP_ROOT', rtrim($path, DIRECTORY_SEPARATOR));

$env = getenv('LIB_ROOT');
if (!$env) {
	$env = $config['LIB_ROOT'];
}
$path = realpath($env);
if (!$path) {
	$path = realpath(dirname(__FILE__) . $env);
}
if (!$path) {
	throw new Exception("'LIB_ROOT' not found: $env");
}
define('LIB_ROOT', rtrim($path, DIRECTORY_SEPARATOR));

$env = getenv('ZEND_ROOT');
if (!$env) {
	$env = $config['ZEND_ROOT'];
}
$path = realpath($env);
if (!$path) {
	$path = realpath(dirname(__FILE__) . $env);
}
if (!$path) {
	throw new Exception("'ZEND_ROOT' not found: " . dirname(__FILE__) . $env);
}
define('ZEND_ROOT', rtrim($path, DIRECTORY_SEPARATOR));

define('DOC_ROOT', rtrim(dirname(__FILE__), DIRECTORY_SEPARATOR));

$env = getenv('WEB_ROOT');
if (!$env) {
	$protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';
	$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;
	if (!$host) {
		throw new Exception("Host not found: $host");
	}
	$env = "$protocol://$host";
}
define('WEB_ROOT', $env);
define('PROJECT_ROOT', rtrim(realpath(APP_ROOT . '/..'), DIRECTORY_SEPARATOR));
define('CORE_NULL', null);
define('POST', 'POST');
define('GET', 'GET');
define('EMPTY_GIF', '/img/empty.gif');
define('EMPTY_GIF_PATH', DOC_ROOT . EMPTY_GIF);

$env = getenv('CACHE_ENABLED');
define('CACHE_ENABLED', $env !== false ? $env : $config['CACHE_ENABLED']);

require_once LIB_ROOT . '/Core/Cookie.php';
if (Core_Cookie::get('DEBUG') == 1) {
	$env = 1;
} else {
	$env = getenv('DEBUG');
}
define('DEBUG', $env !== false ? $env : $config['DEBUG']);
$includePath = get_include_path();
if (strpos(PATH_SEPARATOR . $includePath . PATH_SEPARATOR, PATH_SEPARATOR . ZEND_ROOT . PATH_SEPARATOR) === false) {
	set_include_path(ZEND_ROOT . PATH_SEPARATOR . $includePath);
}