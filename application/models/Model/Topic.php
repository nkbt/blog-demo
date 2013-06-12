<?php

class Model_Topic extends Core_Model
{


    protected $_tableName = 'topic';


    public function onRestoreComment()
    {

    }


    public function onDeleteComment()
    {

    }


    public function getSelectData($filter)
    {

        return $this->fetchPairs($this->_table->getPrimaryKey(), 'title', $filter, array('title' => 'asc'));
    }


    public function updateCounts()
    {

        foreach ($this->fetchAll() as $topicEntity) {

            $topicEntity->countComment = Core_Model_Factory::get('Comment')
                ->fetchCount(
                    array(
                        'idTopic'   => $topicEntity->id,
                        'isDeleted' => false,
                    )
                );

        }

    }

}