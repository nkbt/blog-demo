<?php

/**
 * @property int                    idTopic
 * @property int                    idUser
 * @property string                 title
 * @property string                 text
 * @property int                    timestampAdd
 * @property bool                   isDeleted
 * @property Model_User_Entity      user
 * @property Model_Comment_Entity[] commentList
 * @property int                    countComment
 */
class Model_Topic_Entity extends Core_Model_Entity
{


    protected $_idTopic;
    protected $_idUser;
    protected $_title;
    protected $_text;
    protected $_timestampAdd;
    protected $_isDeleted;
    protected $__user;
    protected $__commentList;
    protected $__countComment;


    protected function _getId()
    {

        return $this->idTopic;
    }


    protected function _setId($value)
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


    protected function _setTitle($value)
    {

        return (string)$value;
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


    protected function _getCommentList()
    {

        try {
            return Core_Model_Factory::get('Comment')
                ->fetchAll(array('idTopic' => $this->id), array('timestampAdd' => 'desc'));
        } catch (Core_Model_Exception_Empty $exc) {
            return array();
        }
    }


    protected function _getCountComment()
    {

        return Zend_Registry::get('Redis')
            ->get("Counter:Topic:$this->idTopic:Comment");
    }


    protected function _setCountComment($count)
    {

        Zend_Registry::get('Redis')
            ->set("Counter:Topic:$this->idTopic:Comment", (int)$count);

        return (int)$count;
    }


}