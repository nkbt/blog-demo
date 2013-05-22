<?php

/**
 * @property string name Event name
 * @property string php  Serialized data for PHP usag
 * @property string node Simple array to be used in Node
 */
class Model_Api_Event_Entity extends Core_Model_Entity
{


    protected $_name;
    protected $_php = array();
    protected $_node = array();


    protected function _getId()
    {

        return null;
    }


    protected function _setId($value)
    {

        return null;
    }


}