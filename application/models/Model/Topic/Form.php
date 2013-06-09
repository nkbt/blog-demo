<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Nikita
 * Date: 9/06/13
 * Time: 6:45 PM
 * To change this template use File | Settings | File Templates.
 */

class Model_Topic_Form extends Custom_Form
{


    public function init()
    {

        $this->addElement(
            'select', 'id_user', array(
                'label'      => 'User',
                'entity'     => 'User',
                'required'   => true,
                'validators' => array(
                    array('validator' => 'Int'),
                ),
                'filters' => array(
                    array('filter' => 'Int'),
                ),
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