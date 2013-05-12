<?php
class Custom_Log
{


    const EMERG = 0; // Emergency: system is unusable
    const ALERT = 1; // Alert: action must be taken immediately
    const CRIT = 2; // Critical: critical conditions
    const ERR = 3; // Error: error conditions
    const WARN = 4; // Warning: warning conditions
    const NOTICE = 5; // Notice: normal but significant condition
    const INFO = 6; // Informational: informational messages
    const DEBUG = 7; // Debug: debug messages
    const MODEL = 101;
    const APP = 102;
    const COLLECTOR = 103;
    const MAIL = 104;
    const MODEL_PLUGIN = 105;
    /**
     * @var self
     */
    protected static $_instance = null;
    /**
     * @var array
     */
    protected $_priorities = array();


    protected function __construct()
    {

        $r = new ReflectionClass($this);
        $this->_priorities = array_flip($r->getConstants());
    }


    public static function shutDown()
    {

        $error = error_get_last();
        if ($error !== null) {
            self::logError($error["type"], $error["message"], $error["file"], $error["line"], array());
        }
    }


    public static function logError($errorCode, $message, $file, $line, $context)
    {

        if (($errorCode != E_ERROR || $errorCode != E_USER_ERROR)
            && (strstr($file, DIRECTORY_SEPARATOR . 'Zend') !== false
                || strstr($file, 'Autoloader.php') !== false)
        ) {
            return;
        }

        try {
            throw new Exception("$message [Error $errorCode], $file:$line\n\n");
        } catch (Exception $ex) {
            $logMessage = $ex->getMessage();
            $logMessage .= "\n\n";
            $logMessage .= date('Y-m-d H:i:s');
            $logMessage .= "\n\n";
            $logMessage .= "TRACE:\n";
            $logMessage .= $ex->getTraceAsString();
            $logMessage .= "\n\n";

            if (!empty($context)) {
                $contextString = array();
                $fullContext = print_r($context, true);
                foreach (explode("\n", $fullContext) as $line) {
                    $trimmed = trim($line, ' ()');
                    if (substr($line, 0, 16) !== "                " && !empty($trimmed) && $trimmed !== '*RECURSION*' && $trimmed !== 'Array') {
                        $contextString[] = str_replace('        ', '  ', substr($line, 4));
                    }
                }
                $reducedContext = implode("\n", $contextString);

                $logMessage .= "REDUCED CONTEXT:\n";
                $logMessage .= $reducedContext;
            }

            Zend_Registry::get('Redis')->hSet("Log:PHP:" . date("Y-m-d"), $ex->getMessage(), $logMessage);
        }
    }


    public static function dbLog($message, $priority = self::APP, $data = null)
    {

        if (is_null(self::$_instance)) {
            self::$_instance = new Custom_Log();
        }

        if ($message instanceof Exception) {
            /** @var Exception $message */
            $messageText = $message->getMessage();
            $trace = $message->getTraceAsString();
            $file = $message->getFile();
            $line = $message->getLine();
        } else {
            $messageText = $message;
            try {
                throw new Exception();
            } catch (Exception $ex) {

                $trace = $ex->getTraceAsString();
                $traceArray = $ex->getTrace();
                $file = isset($traceArray[1]['file']) ? $traceArray[1]['file'] : 'unknown';
                $line = isset($traceArray[1]['line']) ? $traceArray[1]['line'] : 'unknown';
            }
        }
        $hashKey = "$messageText - $file:$line";

        $id = "Log:PHP:" . date("Y-m-d");
        Zend_Registry::get('Redis')->hSet(
            $id,
            $hashKey,
            Zend_Json::encode(
                array(
                    'priority' => self::$_instance->getPriority($priority),
                    'timestamp' => time(),
                    'message' => $messageText,
                    'trace' => $trace,
                    'data' => $data,
                )
            )
        );

    }


    public function getPriority($priority)
    {

        if (!isset($this->_priorities[$priority])) {
            throw new Core_Exception('Bad log priority');
        }

        return $this->_priorities[$priority];
    }
}