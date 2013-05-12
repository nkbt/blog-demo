<?php
class Core_Model_Exception_Empty extends Core_Model_Exception
{


    public function __construct($msg = '', $code = 0, Exception $previous = null)
    {

        if (empty($msg)) {
            $msg = "Select returned empty result";
        }
        parent::__construct($msg, $code, $previous);
    }


}