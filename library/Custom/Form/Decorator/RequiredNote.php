<?php
class Custom_Form_Decorator_RequiredNote extends Zend_Form_Decorator_Abstract
{


    public function render($content)
    {

        $markup = '<p class="form-requiredNote required">* Required fields</p>';

        switch ($this->getPlacement()) {
            case self::PREPEND:
                return $markup . $content;
            case self::APPEND:
            default:
                return $content . $markup;
        }
    }
}
