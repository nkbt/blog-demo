<?php
//require_once 'Zend/Exception.php';
class Custom_Exception extends Zend_Exception
{


    protected $message = 'Exception';


    public function __construct($message = null, $code = 0)
    {

        if (is_null($message)) {
            $message = $this->message;
        }
        parent::__construct($message, $code);
    }
}