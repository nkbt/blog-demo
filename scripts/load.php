<?php
$config = array(
	'APP_ENV' => 'development',
	'APP_ROOT' => '/../application',
	'LIB_ROOT' => '/../library',
	'ZEND_ROOT' => '/../library',
	'CACHE_ENABLED' => 0,
	'DEBUG' => 0,
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

// Initialize the application path and autoloading
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();

// Define some CLI options
$getopt = new Zend_Console_Getopt(array(
    'withdata|w' => 'Load database with sample data',
    'env|e-s' => 'Application environment for which to create database (defaults to development)',
    'help|h' => 'Help -- usage message',
));
try {
    $getopt->parse();
} catch (Zend_Console_Getopt_Exception $e) {
    // Bad options passed: report usage
    echo $e->getUsageMessage();

    return false;
}

// If help requested, report usage message
if ($getopt->getOption('h')) {
    echo $getopt->getUsageMessage();

    return true;
}

// Initialize values based on presence or absence of CLI options
$withData = $getopt->getOption('w');
$env = $getopt->getOption('e');
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (null === $env) ? 'development' : $env);

// Initialize Zend_Application
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

// Initialize and retrieve DB resource
$bootstrap = $application->getBootstrap();
$bootstrap->bootstrap('db');
$dbAdapter = $bootstrap->getResource('db');

// let the user know whats going on (we are actually creating a
// database here)
if ('testing' != APPLICATION_ENV) {
    echo 'Writing Database in (control-c to cancel): ' . PHP_EOL;
}

// Check to see if we have a database file already
$options = $bootstrap->getOption('resources');

// this block executes the actual statements that were loaded from
// the schema file.
try {
    $schemaSql = file_get_contents(dirname(__FILE__) . '/schema.sql');
    // use the connection directly to load sql in batches
    $dbAdapter->getConnection()->exec($schemaSql);

    if ('testing' != APPLICATION_ENV) {
        echo PHP_EOL;
        echo 'Database Created';
        echo PHP_EOL;
    }

    if ($withData) {
        $dataSql = file_get_contents(dirname(__FILE__) . '/data.sql');
        // use the connection directly to load sql in batches
        $dbAdapter->getConnection()->exec($dataSql);
        if ('testing' != APPLICATION_ENV) {
            echo 'Data Loaded.';
            echo PHP_EOL;
        }
    }

} catch (Exception $e) {
    echo 'AN ERROR HAS OCCURED:' . PHP_EOL;
    echo $e->getMessage() . PHP_EOL;

    return false;
}

// generally speaking, this script will be run from the command line
return true;