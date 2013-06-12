<?php


class Model_Topic_Form_Edit extends Model_Topic_Form
{


    public function init()
    {

        parent::init();
        
        $this->_addIdElement('id_topic');
        $this->setAction(
            $this->getView()
                ->url(array('controller' => 'topic', 'action' => 'edit'), 'default', true)
        );
    }
}