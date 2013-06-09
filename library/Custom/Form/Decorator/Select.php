<?php

class Custom_Form_Decorator_Select extends Zend_Form_Decorator_Abstract
{


    public function render($content)
    {

        /** @var $element Custom_Form_Element_Select */
        $element = $this->getElement();

        $options = $element->getAttrib('options');

        if (!empty($options)) {
            return $content;
        }
        
        if ($element->getHasEmpty()) {
            $label = $element->getEmptyLabel();
            if (!$label) {
                $label = '----';
            }
            $options = array(
                null => $label,
            );
        } else {
            $options = array();
        }
        
        $entity = $element->getEntity();
        if (!is_null($entity)) {
            $options += Core_Model_Factory::get($entity)->getSelectData($element->getValuesFilter());
        } else {
            $enumEntity = $element->getEnumEntity();
            $enumField  = $element->getEnumField();
            if (!is_null($enumEntity) && !is_null($enumField)) {
                $options += Core_Model_Factory::get($enumEntity)->getEnumValues($enumField);
            }
        }
        
        $element->setAttrib('options', $options);
        if ($element->getIsDisableEmptyValue()) {
            $element->setAttrib('disable', array(null));
        }
        
        return $content;
    }
}