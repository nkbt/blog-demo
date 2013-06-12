<?php

class Model_User extends Core_Model
{


    protected $_tableName = 'user';


    public function updateCounts()
    {

        foreach ($this->fetchAll() as $userEntity) {

            $userEntity->countComment = Core_Model_Factory::get('Comment')
                ->fetchCount(
                    array(
                        'idUser'   => $userEntity->id,
                        'isDeleted' => false,
                    )
                );

            $userEntity->countTopic = Core_Model_Factory::get('Topic')
                ->fetchCount(
                    array(
                        'idUser'   => $userEntity->id,
                        'isDeleted' => false,
                    )
                );
        }

    }


    public function onRestoreTopic()
    {

    }


    public function onDeleteTopic()
    {

    }


}