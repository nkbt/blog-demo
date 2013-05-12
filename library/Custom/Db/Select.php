<?php
//require_once 'Zend/Db/Select.php';
class Custom_Db_Select extends Zend_Db_Select
{


    public function __sleep()
    {

        $allVariables = get_object_vars($this);
        unset($allVariables['_adapter']);
        $serializable = array_keys($allVariables);

        return $serializable;
    }


    public function __wakeup()
    {

        $this->_adapter = Zend_Registry::get('dbAdapter');
    }
}
