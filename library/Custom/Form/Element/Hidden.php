<?php
class Custom_Form_Element_Hidden extends Zend_Form_Element_Hidden
{


    public function setDecorators(array $decorators)
    {

        $this->clearDecorators();

        return $this->addDecorators(array('ViewHelper'));
    }

}
