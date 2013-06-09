<?php

class Custom_Form_Decorator_ViewHelper extends Zend_Form_Decorator_ViewHelper
{


    /**
     * Retrieve element attributes
     *
     * Set id to element name and/or array item.
     *
     * @return array
     */
    public function getElementAttribs()
    {

        if (null === ($element = $this->getElement())) {
            return null;
        }

        $attribs = $element->getAttribs();
        if (isset($attribs['helper'])) {
            unset($attribs['helper']);
        }

        if (isset($attribs['placeholder'])) {
            $translator = $element->getTranslator();
            if (!is_null($translator)) {
                $attribs['placeholder'] = $translator->translate($attribs['placeholder']);
            }
            if (!isset($attribs['title'])) {
                $attribs['title'] = $attribs['placeholder'];
            }
        }

        if (method_exists($element, 'getSeparator')) {
            if (null !== ($listsep = $element->getSeparator())) {
                $attribs['listsep'] = $listsep;
            }
        }

        if (isset($attribs['id'])) {
            return $attribs;
        }

        $id = $element->getName();

        if ($element instanceof Zend_Form_Element) {
            if (null !== ($belongsTo = $element->getBelongsTo())) {
                $belongsTo = preg_replace('/\[([^\]]+)\]/', '-$1', $belongsTo);
                $id        = $belongsTo . '-' . $id;
            }
        }

        $element->setAttrib('id', $id);
        $attribs['id'] = $id;

        return $attribs;
    }
}
