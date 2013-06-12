<?php


class Model_Comment_Form_Edit extends Model_Comment_Form
{


    public function init()
    {

        parent::init();

        $this->_addIdElement('id_comment');
        $this->setAction(
            $this->getView()
                ->url(array('controller' => 'comment', 'action' => 'edit'), 'default', true)
        );
    }
}