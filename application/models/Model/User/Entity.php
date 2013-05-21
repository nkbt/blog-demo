<?php

/**
 * @property int                    idUser
 * @property string                 name
 * @property string                 email
 * @property int                    timestampAdd
 * @property bool                   isDeleted
 * @property Model_Comment_Entity[] commentList
 */
class Model_User_Entity extends Core_Model_Entity
{


    protected $_idUser;
    protected $_name;
    protected $_email;
    protected $_timestampAdd;
    protected $_isDeleted;
    protected $__commentList;


    protected function _getId()
    {

        return $this->idUser;
    }


    protected function _setId($value)
    {

        return (int)$value;
    }


    protected function _setIdUser($value)
    {

        return (int)$value;
    }


    protected function _setName($value)
    {

        return (string)$value;
    }


    protected function _setEmail($value)
    {

        return (string)$value;
    }


    protected function _setTimestampAdd($value)
    {

        if (is_string($value)) {
            return strtotime($value);
        }

        return (int)$value;
    }


    protected function _setIsDeleted($value)
    {

        return (bool)$value;
    }


    protected function _getCommentList()
    {

        try {
            return Core_Model_Factory::get('Comment')->fetchAll(array('idUser' => $this->id), array('timestampAdd' => 'desc'));
        } catch (Core_Model_Exception_Empty $exc) {
            return array();
        }
    }
}