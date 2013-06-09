<?php
/**
 */
class Custom_Form extends Zend_Form
{


    const BUTTON_SUBMIT = 'submit';
    const BUTTON_CANCEL = 'cancel';
    const BUTTON_RESET  = 'reset';
    const BUTTON_SINGLE = 'single';


    public function __construct($options = null)
    {

        $this->addPrefixPath('Custom_Form_Element', 'Custom/Form/Element/', 'element');
        $this->addPrefixPath('Custom_Form_Decorator', 'Custom/Form/Decorator/', 'decorator');
        $this->addElementPrefixPath('Custom_Form_Decorator', 'Custom/Form/Decorator/', 'decorator');
        $this->addElementPrefixPath('Custom_Validate', 'Custom/Validate/', 'validate');
        $this->addElementPrefixPath('Custom_Filter', 'Custom/Filter/', 'filter');

        $this->setMethod(Custom_Form::METHOD_POST);
        $this->setAttrib('class', 'form-form');

        parent::__construct($options);
    }


    /**
     * @return Custom_Form
     */
    public function loadDefaultDecorators()
    {

        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return $this;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {


            $this->setDecorators(
                array(
                    array(array('formElements' => 'FormElements')),
                    array(array('requiredNote' => 'RequiredNote')),
                    array(
                        array('formButtons' => 'FormButtons'), array(
                        'button' => array(
                            Custom_Form::BUTTON_SUBMIT => array('tag' => 'input', 'type' => 'submit', 'title' => 'Save', 'class' => 'form-submit btn btn-primary'),
                            Custom_Form::BUTTON_RESET  => array('tag' => 'input', 'type' => 'reset', 'title' => 'Reset', 'class' => 'form-reset btn'),
                        )
                    )
                    ),
                    'FormHiddenSubmit',
                    array(array('elementsContainer' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-body')),
                    'Form',
                )
            );
        }

        $this->setElementFilters(array('StripTags', 'StripNewlines', 'StringTrim', 'Null'));

        $this->setElementDecorators(
            array(
                array(array('field' => 'ViewHelper')),
                array(array('errors' => 'Errors'), array(
                    'class' => 'text-error',
                    'elementStart' => '<div%s><em>',
                    'elementEnd' => '</em></div>',
                    'elementSeparator' => '</em><br><em>',
                )),      
                'Description',
                array(
                    array('fieldContainer' => 'HtmlTag'),
                    array('tag' => 'span', 'class' => 'form-field')
                ),
                array(array('labelContainer' => 'Label'), array(
                    'class' => 'form-label',
                    'requiredSuffix' => ' *',
                )),
                array(
                    array('clearDiv' => 'HtmlTag'),
                    array('placement' => Zend_Form_Decorator_HtmlTag::APPEND, 'tag' => 'div', 'class' => 'clear')
                ),
                array(
                    array('elementContainer' => 'HtmlTag'),
                    array('tag' => 'div', 'class' => 'form-element')
                ),
            )
        );

        return $this;
    }


    /**
     * Fix for native zend method to properly pick up pre-set element filters and decorators.
     *
     * @param string|Zend_Form_Element $element
     * @param null                     $name
     * @param null                     $options
     *
     * @return $this|Zend_Form
     * @throws Zend_Form_Exception
     */
    public function addElement($element, $name = null, $options = null)
    {

        if (is_string($element)) {
            if (null === $name) {
                throw new Zend_Form_Exception('Elements specified by string must have an accompanying name');
            }

            if (is_array($this->_elementDecorators)) {
                if (null === $options) {
                    $options = array('decorators' => $this->_elementDecorators);
                } elseif ($options instanceof Zend_Config) {
                    $options = $options->toArray();
                }
                if (is_array($options)
                    && !array_key_exists('decorators', $options)
                ) {
                    $options['decorators'] = $this->_elementDecorators;
                }
            }

            if (is_array($this->_elementFilters)) {
                if (null === $options) {
                    $options = array('filters' => $this->_elementFilters);
                } elseif ($options instanceof Zend_Config) {
                    $options = $options->toArray();
                }
                if (is_array($options)
                    && !array_key_exists('filters', $options)
                ) {
                    $options['filters'] = $this->_elementFilters;
                }
            }

            $this->_elements[$name] = $this->createElement($element, $name, $options);
        } elseif ($element instanceof Zend_Form_Element) {
            $prefixPaths              = array();
            $prefixPaths['decorator'] = $this->getPluginLoader('decorator')->getPaths();
            if (!empty($this->_elementPrefixPaths)) {
                $prefixPaths = array_merge($prefixPaths, $this->_elementPrefixPaths);
            }

            if (null === $name) {
                $name = $element->getName();
            }

            $this->_elements[$name] = $element;
            $this->_elements[$name]->addPrefixPaths($prefixPaths);
        } else {
            throw new Zend_Form_Exception('Element must be specified by string or Zend_Form_Element instance');
        }

        $this->_order[$name] = $this->_elements[$name]->getOrder();
        $this->_orderUpdated = true;
        $this->_setElementsBelongTo($name);

        return $this;
    }


    protected function _addIdElement($name)
    {

        $this->addElement(
            'hidden', $name, array(
                'required'   => true,
                'validators' => array('Int'),
                'decorators' => array('ViewHelper'),
                'filters'    => array('Null'),
            )
        );
    }
}