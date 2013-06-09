<?php

class Custom_Form_Decorator_Label extends Zend_Form_Decorator_Label
{

    public function getLabel()
    {

        if (null === ($element = $this->getElement())) {
            return '';
        }

        $label = $element->getLabel();
        $label = trim($label);

        if (empty($label)) {
            return '';
        }
        # ZEND BUG! Removing double translation! $element->getLabel() returns already translated value.
        //		if (null !== ($translator = $element->getTranslator())) {
        //			$label = $translator->translate($label);
        //		}

        $optPrefix = $this->getOptPrefix();
        $optSuffix = $this->getOptSuffix();
        $reqPrefix = $this->getReqPrefix();
        $reqSuffix = $this->getReqSuffix();
        $separator = $this->getSeparator();

        if (!empty($label)) {
            if ($element->isRequired()) {
                $label = $reqPrefix . $label . $reqSuffix;
            } else {
                $label = $optPrefix . $label . $optSuffix;
            }
        }

        return $label;
    }
}
