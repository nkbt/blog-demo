<?php
class Core_Model_Table extends Zend_Db_Table
{


    protected $_rowClass = 'Core_Model_Row';


    public function getPrimaryKey()
    {

        return current($this->info(self::PRIMARY));
    }


    public function getEnumValues($column)
    {

        return array(null => 'No implemented');
    }
}
