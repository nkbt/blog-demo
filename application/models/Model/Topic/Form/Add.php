<?php


class Model_Topic_Form_Add extends Model_Topic_Form
{


    public function init()
    {

        parent::init();
        
        $this->setAction(
            $this->getView()
                ->url(array('controller' => 'topic', 'action' => 'add'), 'default', true)
        );
    }

}