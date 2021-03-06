<?php

/**
 * @property int                    idUser
 * @property string                 name
 * @property string                 email
 * @property int                    timestampAdd
 * @property bool                   isDeleted
 * @property Model_Comment_Entity[] commentList
 * @property int                    countComment
 * @property int                    countTopic
 */
class Model_User_Entity extends Core_Model_Entity
{


    protected $_idUser;
    protected $_name;
    protected $_email;
    protected $_timestampAdd;
    protected $_isDeleted;
    protected $__commentList;
    protected $__countComment;
    protected $__countTopic;


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


    protected function _getCountComment()
    {

        return Zend_Registry::get('Redis')
            ->get("Counter:User:$this->idUser:Comment");
    }


    protected function _setCountComment($count)
    {

        Zend_Registry::get('Redis')
            ->set("Counter:User:$this->idUser:Comment", (int)$count);

        return (int)$count;
    }


    protected function _getCountTopic()
    {

        return Zend_Registry::get('Redis')
            ->get("Counter:User:$this->idUser:Topic");
    }


    protected function _setCountTopic($count)
    {

        Zend_Registry::get('Redis')
            ->set("Counter:User:$this->idUser:Topic", (int)$count);

        return (int)$count;
    }
}