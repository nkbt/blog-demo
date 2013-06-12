<?php

class Model_Comment_Form extends Custom_Form
{


    public function init()
    {

        $this->addElement(
            'select', 'id_user', array(
                'label'      => 'Author',
                'entity'     => 'User',
                'required'   => true,
                'validators' => array('Int'),
                'filters'    => array('Int', 'Null'),
            )
        );
        $this->addElement(
            'select', 'id_topic', array(
                'label'      => 'Topic',
                'entity'     => 'Topic',
                'required'   => true,
                'validators' => array('Int'),
                'filters'    => array('Int', 'Null'),
            )
        );
        $this->addElement(
            'text', 'text', array(
                'label'      => 'Message',
                'required'   => true,
                'validators' => array(
                    array('validator' => 'StringLength', 'options' => array(2, 200)),
                )
            )
        );
    }

}