<?php

class Model_Topic_Form extends Custom_Form
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
            'text', 'title', array(
                'label'      => 'Title',
                'required'   => true,
                'validators' => array(
                    array('validator' => 'StringLength', 'options' => array(2, 100)),
                    array('validator' => 'Alnum', 'options' => array('allowWhiteSpace' => true)),
                )
            )
        );
        $this->addElement(
            'textarea', 'text', array(
                'label'      => 'Title',
                'required'   => true,
                'attribs'    => array(
                    'rows' => 4,
                    'cols' => 50,
                ),
                'validators' => array(
                    array('validator' => 'StringLength', 'options' => array(5, 1000)),
                    array('StringLength', false, array('allowWhiteSpace' => true)),
                )
            )
        );
    }

}