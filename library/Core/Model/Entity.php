<?php



/**
 * @property int id
 */
abstract class Core_Model_Entity
{


    protected $__id;
    /**
     * Even if property is NULL, we don't want to calculate it more then once
     *
     * @var array
     */
    private $__setCache = array();


    public function __construct()
    {

        if (APP_ENV === 'development') {
            $className = get_class($this);

            $modelClass = str_replace('_Entity', '', $className);

            if (class_exists($modelClass)) {
                try {
                    throw new Core_Model_Entity_Exception();
                } catch (Core_Model_Entity_Exception $exc) {
                    $trace = $exc->getTrace();
                    if (!isset($trace[1]) || !isset($trace[1]['class']) || $trace[1]['class'] !== 'Core_Model') {
                        throw new Core_Model_Entity_Exception("Entity must be created by Model::createEntity() method");
                    }
                }
            }

            $reflection = new ReflectionClass($this);
            $comment    = $reflection->getDocComment();
            foreach ($reflection->getDefaultProperties() as $property => $value) {
                $publicName = trim($property, '_');
                if ($publicName !== 'id' && !preg_match("~\@property\s+[a-zA-Z_\[\]]+\s+$publicName~m", $comment)) {
                    throw new Core_Model_Entity_Exception("Class comment '@property propertyType $publicName' is missing in $className");
                }
            }
        }
    }


    public function __sleep()
    {

        $fields  = get_object_vars($this);
        $allowed = array();
        foreach ($fields as $field => $value) {
            // allow to serialize only protected properties and not private
            if (substr($field, 0, 2) !== '__') {
                $allowed[] = $field;
            }
        }

        return $allowed;
    }


    public function __get($name)
    {

        $property = "_$name";

        if (!property_exists($this, $property)) {
            $property = "__$name";
            if (!property_exists($this, $property)) {
                throw new Core_Model_Entity_Exception("Property '$name' does not exist");
            }
        }

        if (array_key_exists($property, $this->__setCache)) {
            return $this->$property;
        }

        $getter = '_get' . ucfirst($name);
        if (method_exists($this, $getter)) {
            $this->$property             = $this->$getter();
            $this->__setCache[$property] = true;
        }

        return $this->$property;
    }


    /**
     * @param $name
     * @param $value
     *
     * @return Core_Model_Entity
     * @throws Core_Model_Entity_Exception
     */
    public function __set($name, $value)
    {

        $property = "_$name";

        if (!property_exists($this, $property)) {
            $property = "__$name";
            if (!property_exists($this, $property)) {
                throw new Core_Model_Entity_Exception("Property '$name' does not exist");
            }
        }

        $setter = '_set' . ucfirst($name);
        if (method_exists($this, $setter)) {
            $this->$property = $this->$setter($value);
        } else {
            $this->$name = $value;
        }
        $this->__setCache[$property] = true;

        return $this;
    }


    /**
     * @return array
     */
    final public function toArray()
    {

        $allVars = get_object_vars($this);
        $data    = array();
        foreach ($allVars as $property => $value) {
            if (preg_match('~^_{1}[a-z0-9]+$~i', $property)) {
                $publicProperty = trim($property, '_');
                $key            = preg_replace('~([A-Z])~e', "'_' . strtolower('\\1')", $publicProperty);

                $data[$key] = $this->_toArray($this->$publicProperty);
            }
        }

        return $data;
    }


    /**
     * @param array $data
     *
     * @return Core_Model_Entity
     */
    final public function fromArray(array $data)
    {

        foreach ($data as $key => $value) {
            $publicProperty        = preg_replace('~_([a-z])~e', "strtoupper('\\1')", $key);
            $this->$publicProperty = $value;
        }

        return $this;
    }


    protected function _toArray($property)
    {

        if (is_array($property)) {
            $result = array();
            foreach ($property as $key => $value) {
                $result[$key] = $this->_toArray($value);
            }

            return $result;
        }

        if (is_object($property) && method_exists($property, 'toArray')) {
            return $property->toArray();
        }

        return $property;
    }


    abstract protected function _getId();


}