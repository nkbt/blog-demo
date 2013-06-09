<?php

class Custom_Form_Decorator_FormHiddenSubmit extends Zend_Form_Decorator_Abstract
{


    public function render($content)
    {

        switch ($this->getPlacement()) {
            case self::PREPEND:
                return $this->_getMarkup() . $content;
            case self::APPEND:
            default:
                return $content . $this->_getMarkup();
        }
    }


    protected function _getMarkup()
    {

        $view  = $this
            ->getElement()
            ->getView();
        $xhtml = $view->formSubmit(null, null, array('class' => 'hidden'));

        return $xhtml;
    }
}