<?php


class Model_Comment_Form_Add extends Model_Comment_Form
{


    public function init()
    {

        parent::init();

        $this->setAction(
            $this->getView()
                ->url(array('controller' => 'comment', 'action' => 'add'), 'default', true)
        );
    }


}