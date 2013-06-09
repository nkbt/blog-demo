<?php

class Custom_Form_Decorator_HtmlTag extends Zend_Form_Decorator_HtmlTag
{


    /**
     * Render content wrapped in an HTML tag
     *
     * @param  string $content
     *
     * @return string
     */
    public function render($content)
    {

        $openOnly        = $this->getOption('openOnly');
        $closeOnly       = $this->getOption('closeOnly');
        $internalContent = $this->getOption('content');
        if ($openOnly || $closeOnly || !$internalContent) {
            return parent::render($content);
        }

        $placement = $this->getPlacement();
        switch ($placement) {
            case self::APPEND:
                $this->_placement = null;

                return $content . parent::render($internalContent);
            case self::PREPEND:
                $this->_placement = null;

                return parent::render($internalContent) . $content;
            default:
                return parent::render($content);
        }
    }


}
