<?php
class Core_Model_Exception_Select extends Core_Model_Exception
{


    public function __construct($msg = '', $code = 0, Exception $previous = null)
    {

        if (empty($msg)) {
            $msg = "Select error";
        }
        parent::__construct($msg, $code, $previous);
    }


}