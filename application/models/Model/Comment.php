<?php

class Model_Comment extends Core_Model
{


    protected $_tableName = 'comment';


    public function onRestoreTopic($data)
    {


    }


    public function onDeleteTopic($data)
    {

        $id = $data['id'];
        try {
            $commentList = $this->fetchAll(array('idTopic' => $id));
            foreach ($commentList as $commentEntity) {
                $this->delete($commentEntity);
            }
        } catch (Core_Model_Exception_Empty $exc) {

        }
    }

}