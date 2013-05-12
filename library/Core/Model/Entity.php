<?php



/**
 * @property int id
 */
abstract class Core_Model_Entity
{


    protected $__id;
    /**
     * We don't want to accidentally store in cache related entities, that could be changed
     *
     * @var array
     */
    private $__clearBeforeCache = array();
    /**
     * Even if property is NULL, we don't want to calculate it more then once
     *
     * @var array
     */
    private $__setCache = array();


    public function __construct()
    {

        if (APPLICATION_ENV === 'development') {

            try {
                throw new Core_Model_Entity_Exception();
            } catch (Core_Model_Entity_Exception $exc) {
                $trace = $exc->getTrace();
                if (!isset($trace[1]) || !isset($trace[1]['class']) || $trace[1]['class'] !== 'Core_Model') {
                    throw new Core_Model_Entity_Exception("Entity must be created by Model::createEntity() method");
                }
            }

            $reflection = new ReflectionClass($this);
            $comment = $reflection->getDocComment();
            foreach ($reflection->getDefaultProperties() as $property => $value) {
                $publicName = trim($property, '_');
                if ($publicName !== 'id' && !preg_match("~\@property\s+[a-zA-Z_]+\s+$publicName~m", $comment)) {
                    throw new Core_Model_Entity_Exception("Class comment '@property propertyType $publicName' is missing");
                }
            }
        }
    }


    public function __sleep()
    {

        $allVars = get_object_vars($this);
        foreach ($this->__clearBeforeCache as $field => $value) {
            unset($allVars[$field]);
        }
        $serializable = array_keys($allVars);

        return $serializable;
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
            $this->$property = $this->$getter();
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
        $data = array();
        foreach ($allVars as $property => $value) {
            if (preg_match('~^_{1}[a-z0-9]+$~i', $property)) {
                $publicProperty = trim($property, '_');
                $key = preg_replace('~([A-Z])~e', "'_' . strtolower('\\1')", $publicProperty);
                $data[$key] = $this->$publicProperty;
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
            $publicProperty = preg_replace('~_([a-z])~e', "strtoupper('\\1')", $key);
            $this->$publicProperty = $value;
        }

        return $this;
    }


    abstract protected function _getId();


    /**
     * @param string $clearBeforeCache
     *
     * @return Core_Model_Entity
     */
    protected function _setClearBeforeCache($clearBeforeCache)
    {

        $this->__clearBeforeCache[$clearBeforeCache] = true;

        return $this;
    }


}