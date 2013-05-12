<?php

/**
 * @property int                idComment
 * @property int                idTopic
 * @property int                idUser
 * @property string             text
 * @property int                timestampAdd
 * @property bool               isDeleted
 * @property Model_User_Entity  user
 * @property Model_Topic_Entity topic
 */
class Model_Comment_Entity extends Core_Model_Entity
{


    protected $_idComment;
    protected $_idTopic;
    protected $_idUser;
    protected $_text;
    protected $_timestampAdd;
    protected $_isDeleted;
    protected $__topic;
    protected $__user;


    protected function _getId()
    {

        return $this->idTopic;
    }


    protected function _setId($value)
    {

        return (int)$value;
    }


    protected function _setIdComment($value)
    {

        return (int)$value;
    }


    protected function _setIdTopic($value)
    {

        return (int)$value;
    }


    protected function _setIdUser($value)
    {

        return (int)$value;
    }


    protected function _setText($value)
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


    protected function _getUser()
    {

        return Core_Model_Factory::get('User')->find($this->idUser);
    }


    protected function _getTopic()
    {

        return Core_Model_Factory::get('Topic')->find($this->idTopic);
    }


}