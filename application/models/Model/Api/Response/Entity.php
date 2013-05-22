<?php

/**
 * @property array messages
 * @property array data
 * @property bool  ok
 */
class Model_Api_Response_Entity extends Core_Model_Entity
{


    protected $_messages = array();
    protected $_data = array();
    protected $_ok = false;


    protected function _getId()
    {

        return null;
    }


    protected function _setId($value)
    {

        return null;
    }


}