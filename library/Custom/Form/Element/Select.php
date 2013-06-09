<?php
class Custom_Form_Element_Select extends Zend_Form_Element_Select
{


    /**
     * Is value translation disabled?
     *
     * @var bool
     */
    protected $_valueTranslatorDisabled = false;
    protected $_entity = null;
    protected $_enumEntity = null;
    protected $_enumField = null;
    protected $_emptyLabel = null;
    protected $_hasEmpty = true;
    protected $_isDisableEmptyValue = null;
    protected $_valuesFilter = array();


    public function render(Zend_View_Interface $view = null)
    {

        $this->_addSelectDecorator();

        return parent::render($view);
    }


    public function getEntity()
    {

        return $this->_entity;
    }


    public function setEntity($entity)
    {

        $this->_entity = ucfirst($entity);

        return $this;
    }


    public function getEnumEntity()
    {

        return $this->_enumEntity;
    }


    public function setEnumEntity($entity)
    {

        $this->_enumEntity = ucfirst($entity);

        return $this;
    }


    public function getEnumField()
    {

        return $this->_enumField;
    }


    public function setEnumField($field)
    {

        $this->_enumField = $field;

        return $this;
    }


    public function getEmptyLabel()
    {

        return $this->_emptyLabel;
    }


    public function setEmptyLabel($label)
    {

        $this->_emptyLabel = $label;

        return $this;
    }


    public function getHasEmpty()
    {

        return $this->_hasEmpty;
    }


    public function setHasEmpty($flag)
    {

        $this->_hasEmpty = $flag;

        return $this;
    }


    public function getIsDisableEmptyValue()
    {

        return $this->_isDisableEmptyValue;
    }


    public function setIsDisableEmptyValue($flag)
    {

        $this->_isDisableEmptyValue = (bool)$flag;

        return $this;
    }


    /**
     * Indicate whether or not value translation should be disabled
     *
     * @param  bool $flag
     *
     * @return Zend_Form_Element
     */
    public function setDisableValueTranslator($flag)
    {

        $this->_valueTranslatorDisabled = (bool)$flag;

        return $this;
    }


    /**
     * Is value translation disabled?
     *
     * @return bool
     */
    public function valueTranslatorIsDisabled()
    {

        return $this->_valueTranslatorDisabled;
    }


    public function isValid($value, $context = null)
    {

        $this->_addSelectDecorator();

        // Prefill data
        $valueDecorator = $this->getDecorator('Select');
        $valueDecorator->setElement($this);
        $valueDecorator->render('');

        return parent::isValid($value, $context);
    }


    /**
     * @return null|array Plugins data
     */
    public function getValuesFilter()
    {

        return $this->_valuesFilter;
    }


    /**
     * @param array $filter
     *
     * @return Custom_Form_Element_Select
     */
    public function setValuesFilter(array $filter)
    {

        $this->_valuesFilter = $filter;

        return $this;
    }


    protected function _addSelectDecorator()
    {

        if (isset($this->_decorators['select'])) {
            return;
        }
        $decorator         = $this->_getDecorator('Select', null);
        $this->_decorators = array_merge(
            array('select' => $decorator),
            $this->getDecorators()
        );
    }


    /**
     * Translate a multi option value
     *
     * @param  string $value
     *
     * @return string
     */
    protected function _translateValue($value)
    {

        if ($this->valueTranslatorIsDisabled()) {
            return $value;
        }

        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = $this->_translateValue($val);
            }

            return $value;
        } else {
            if (null !== ($translator = $this->getTranslator())) {
                return $translator->translate($value);
            }

            return $value;
        }
    }
}
