<?php
class Core_Model_Exception_InvalidInput extends Core_Model_Exception
{


    public function __construct($msg = '', $code = 0, Exception $previous = null)
    {

        if (empty($msg)) {
            $msg = "Invalid input data";
        }
        parent::__construct($msg, $code, $previous);
    }


}