<?php
class Core_Model
{


    /**
     * @var Zend_Db_Table
     */
    protected $_table;
    /**
     * @var string
     */
    protected $_name;
    /**
     * @var string
     */
    protected $_tableName;
    /**
     * @var array
     */
    protected $_filters = array();


    final public function __construct()
    {

        if (APP_ENV === 'development') {

            try {
                throw new Core_Model_Entity_Exception();
            } catch (Core_Model_Entity_Exception $exc) {
                $trace = $exc->getTrace();
                if (!isset($trace[1]) || !isset($trace[1]['class']) || $trace[1]['class'] !== 'Core_Model_Factory') {
                    throw new Core_Model_Exception("Model must be created by Core_Model_Factory::get() method");
                }
            }
        }

        $this->_table = new Core_Model_Table($this->_tableName);
        $this->_name  = str_replace('Model_', '', get_class($this));

        $this->_init();
    }


    final public function getName()
    {

        return $this->_name;
    }


    /**
     * @param array $data
     *
     * @return Core_Model_Entity
     */
    final public function createEntity(array $data = null)
    {

        $className = get_class($this) . '_Entity';
        /** @var Core_Model_Entity $entity */
        $entity = new $className();
        if (!is_null($data)) {
            $entity->fromArray($data);
        }

        return $entity;
    }


    final public function delete(Core_Model_Entity $entity)
    {

        if (property_exists($entity, '_isDeleted')) {
            $entity->isDeleted = true;
            $this->save($entity, array('is_deleted'));
        } else {
            $this->_table->delete($this->_buildWhereByPrimary($entity));
        }
    }


    final public function restore(Core_Model_Entity $entity)
    {

        if (property_exists($entity, '_isDeleted')) {
            $entity->isDeleted = false;
            $this->save($entity, array('is_deleted'));
        }
    }


    /**
     * @param Core_Model_Entity $entity
     *
     * @param array             $allowedColumns Array of DB columns to update
     *
     * @return Core_Model_Entity
     */
    final public function save(Core_Model_Entity $entity, array $allowedColumns = array())
    {

        $data = $entity->toArray();

        $this->_beforeSave($entity, $data);

        $primary  = $this->_table->info(Zend_Db_Table::PRIMARY);
        $where = $this->_buildWhereByPrimary($entity);
        $isUpdate = count($where) === count($primary);

        $allowedData = empty($allowedColumns)
            ? $data
            : array_intersect_key($data, array_flip($allowedColumns));
        
        $allowedData = $this->_dataAdapter($entity, $allowedData);

        if ($isUpdate) {
            unset($allowedData['timestamp_add']);
            if (property_exists($entity, '_timestampEdit')) {
                $allowedData['timestamp_edit'] = new Zend_Db_Expr('NOW()');
            }
            $entityOld = $this->fetchRow(array_intersect_key($data, array_flip($primary)));
            $this->_table->update($allowedData, $where);
            $id = $entity->id;
        } else {
            if (property_exists($entity, '_timestampAdd')) {
                $allowedData['timestamp_add'] = new Zend_Db_Expr('NOW()');
            }
            if (property_exists($entity, '_isDeleted')) {
                $allowedData['is_deleted'] = 0;
            }

            $id = $this->_table->insert($allowedData);
        }

        $this->_afterSave($entity, $id);

        $entity->fromArray($data);
        $entity->id = $id;
        if ($isUpdate && isset($entityOld)) {
            if (!isset($allowedColumns[0]) || $allowedColumns[0] !== 'is_deleted') {
                $this->publish('onUpdate', array('id' => $entity->id, 'entity' => $entity, 'entityOld' => $entityOld));
            } elseif ($entityOld->isDeleted && !$entity->isDeleted) {
                $this->publish('onRestore', array('id' => $entity->id, 'entity' => $entity));
            } elseif (!$entityOld->isDeleted && $entity->isDeleted) {
                $this->publish('onDelete', array('id' => $entity->id, 'entity' => $entity));
            }
        } else {
            $this->publish('onInsert', array('id' => $entity->id, 'entity' => $entity));
        }

        return $entity;
    }


    final public function publish($eventName, $data)
    {

        $ident = Zend_Registry::isRegistered('EventIdent')
            ? Zend_Registry::get('EventIdent')
            : "Event";
        
        $date = date('Y-m-d:His');

        /** @var Redis $redis */
        $client             = Zend_Registry::get('Redis');
        $eventEntity        = new Model_Api_Event_Entity();
        $eventEntity->ident = "$ident:$date - $this->_name - $eventName";
        $eventEntity->name  = $eventName;
        $eventEntity->node  = $data;
        $eventEntity->php   = base64_encode(serialize($data));

        $keyName = $eventName . ":" . $this->getName();

        $logInfo = array(
            'message' => 'PHP event published',
            'date' => date('r'),
            'name' => $keyName,
            'data' => print_r($data, true),
        );
        Zend_Registry::get('Redis')->rPush($eventEntity->ident, Zend_Json::encode($logInfo));

        $client->publish($keyName, Zend_Json::encode($eventEntity));

        return true;
    }


    /**
     * @throws Core_Model_Exception_Empty
     * @throws Core_Model_Exception_Select
     * @return Core_Model_Entity
     */
    final public function find()
    {

        /** @var Zend_Db_Table_Row_Abstract $row */

        try {
            /** @var Zend_Db_Table_Row $row */
            $row = call_user_func_array(array($this->_table, 'find'), func_get_args());
            $row = $row->current();
        } catch (Exception $exc) {
            throw new Core_Model_Exception_Select($exc->getMessage() . ' Data: ' . var_export(func_get_args()));
        }
        if (!$row) {
            throw new Core_Model_Exception_Empty();
        }

        return $this->createEntity()->fromArray($row->toArray());
    }


    /**
     * @param string $col
     * @param array  $filter
     * @param array  $sort
     * @param int    $limit
     * @param int    $offset
     *
     * @throws Core_Model_Exception_Empty
     * @throws Core_Model_Exception_Select
     * @return array
     */
    final public function fetchCol($col, array $filter = array(), array $sort = array(), $limit = null, $offset = null)
    {

        $select = $this->_table->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
        $select->reset(Zend_Db_Table::COLUMNS);
        $select->columns(array($this->_toUnderscore($col)));

        $this->_applyFilter($select, $filter)
            ->_applySort($select, $sort)
            ->_applyLimit($select, $limit, $offset);

        try {
            $data = $this->_table->getAdapter()->fetchCol($select);
        } catch (Exception $exc) {
            throw new Core_Model_Exception_Select($exc->getMessage() . ' Query: ' . $select->assemble());
        }

        if (empty($data)) {
            throw new Core_Model_Exception_Empty();
        }

        return $data;
    }


    /**
     * @param string $first
     * @param string $second
     * @param array  $filter
     * @param array  $sort
     * @param int    $limit
     * @param int    $offset
     *
     * @throws Core_Model_Exception_Empty
     * @throws Core_Model_Exception_Select
     * @return array
     */
    final public function fetchPairs($first, $second, array $filter = array(), array $sort = array(), $limit = null, $offset = null)
    {

        $select = $this->_table->select(Zend_Db_Table::SELECT_WITH_FROM_PART);
        $select->reset(Zend_Db_Table::COLUMNS);
        $select->columns(array($this->_toUnderscore($first), $this->_toUnderscore($second)));

        $this->_applyFilter($select, $filter)
            ->_applySort($select, $sort)
            ->_applyLimit($select, $limit, $offset);

        try {
            $data = $this->_table->getAdapter()->fetchPairs($select);
        } catch (Exception $exc) {
            throw new Core_Model_Exception_Select($exc->getMessage() . ' Query: ' . $select->assemble());
        }

        if (empty($data)) {
            throw new Core_Model_Exception_Empty();
        }

        return $data;
    }


    /**
     * @param string $column
     *
     * @return array
     */
    public function getEnumValues($column)
    {

        return $this->_table->getEnumValues($column);
    }


    /**
     * @param array $filter
     * @param array $sort
     * @param int   $limit
     * @param int   $offset
     *
     * @throws Core_Model_Exception_Empty
     * @throws Core_Model_Exception_Select
     * @return Core_Model_Entity[]
     */
    final public function fetchAll(array $filter = array(), array $sort = array(), $limit = null, $offset = null)
    {

        $select = $this->_table->select();

        $this->_applyFilter($select, $filter)
            ->_applySort($select, $sort)
            ->_applyLimit($select, $limit, $offset);


        /** @var Zend_Db_Table_Rowset $rowset */
        try {
            $rowset = $this->_table->fetchAll($select);
        } catch (Exception $exc) {
            throw new Core_Model_Exception_Select($exc->getMessage() . ' Query: ' . $select->assemble());
        }

        if (!$rowset->count()) {
            throw new Core_Model_Exception_Empty();
        }

        $result = array();

        /** @var Zend_Db_Table_Row_Abstract $row */
        foreach ($rowset as $row) {
            $result[] = $this->createEntity()->fromArray($row->toArray());
        }

        return $result;
    }


    /**
     * @param array $filter
     * @param array $sort
     * @param null  $offset
     *
     * @throws Core_Model_Exception_Empty
     * @throws Core_Model_Exception_Select
     * @return Core_Model_Entity
     */
    final public function fetchRow(array $filter = array(), array $sort = array(), $offset = null)
    {

        $select = $this->_table->select();

        $this->_applyFilter($select, $filter)
            ->_applySort($select, $sort)
            ->_applyLimit($select, 1, $offset);

        /** @var Zend_Db_Table_Row_Abstract $row */
        try {
            $row = $this->_table->fetchRow($select);
        } catch (Exception $exc) {
            throw new Core_Model_Exception_Select($exc->getMessage() . ' Query: ' . $select->assemble());
        }

        if (!$row) {
            throw new Core_Model_Exception_Empty();
        }

        return $this->createEntity()->fromArray($row->toArray());

    }


    public function updateCounts()
    {

    }


    public function getSelectData($filter)
    {

        return $this->fetchPairs($this->_table->getPrimaryKey(), 'name', $filter, array('name' => 'asc'));
    }


    protected function _buildWhereByPrimary(Core_Model_Entity $entity)
    {

        $data    = $entity->toArray();
        $where   = array();
        $primary = $this->_table->info(Zend_Db_Table::PRIMARY);
        foreach ($primary as $key) {
            if (array_key_exists($key, $data) && !is_null($data[$key])) {
                $where[] = $this->_table->getAdapter()->quoteInto("`$this->_tableName`.`$key` = ?", $data[$key]);
            }
        }

        return $where;
    }


    protected function _init()
    {

    }


    /**
     * @param Core_Model_Entity $entity
     * @param array             $data
     *
     * @return array
     */
    protected function _dataAdapter(Core_Model_Entity $entity, $data)
    {

        return $data;
    }


    /**
     * @param Core_Model_Entity $entity
     * @param array             $data
     */
    protected function _beforeSave(Core_Model_Entity $entity, array $data)
    {
    }


    /**
     * @param Core_Model_Entity $entity
     * @param int               $id
     */
    protected function _afterSave(Core_Model_Entity $entity, $id)
    {
    }


    /**
     * @param Zend_Db_Select $select
     * @param array          $filter
     *
     * @return Core_Model
     */
    protected function _applyFilter(Zend_Db_Select $select, array $filter = array())
    {

        $columns = array();
        foreach ($this->_table->info(Zend_Db_Table::COLS) as $col) {
            $columns[$this->_toCamelCase($col)] = $col;
        }

        foreach ($filter as $key => $value) {
            $name     = $this->_toCamelCase($key);
            $callback = "_applyFilter" . ucfirst($name);
            if (method_exists($this, $callback)) {
                $this->$callback($select, $value);
            } else {
                $this->_applyDefaultFilter($select, $name, $value);
            }
        }

        return $this;
    }


    protected function _applyDefaultFilter(Zend_Db_Select $select, $name, $value)
    {

        $filterData = explode('|', $name);
        if (count($filterData) == 2) {
            list($filter, $modifier) = $filterData;
        } else {
            list($filter) = $filterData;
            $modifier = null;
        }

        $column = $this->_toUnderscore($filter);
        if (!in_array($column, $this->_table->info(Zend_Db_Table::COLS))) {
            throw new Core_Model_Exception('Incorrect filter: ' . $name);
        }

        switch ($modifier) {
            case '=':
            case '<':
            case '<=':
            case '>':
            case '>=':
            case '<>':
                $select->where("`$this->_tableName`.`$column` $modifier ?", $value);
                break;

            case '*': # value should be an array
                if (!is_array($value)) {
                    $value = array($value);
                }
                $select->where("`$this->_tableName`.`$column` IN (?)", $value);
                break;

            case '!*': # value should be an array
                if (!is_array($value)) {
                    $value = array($value);
                }
                $select->where("`$this->_tableName`.`$column` NOT IN (?)", $value);
                break;

            case 'null': # NOTE: value is ignored
                $select->where("`$this->_tableName`.`$column` IS NULL");
                break;

            case '!null': # NOTE: value is ignored
                $select->where("`$this->_tableName`.`$column` IS NOT NULL");
                break;

            case 'between': # NOTE: value must be an array with two items
                if (is_array($value) && count($value) == 2) {
                    list($from, $to) = $value;
                    $select->where(
                        $select->getAdapter()->quoteInto("`$this->_tableName`.`$column` BETWEEN ?", $from)
                        . " AND ?", $to
                    );
                }
                break;

            default:
                $select->where("`$this->_tableName`.`$column` = ?", $value);
        }
    }


    protected function _toCamelCase($string)
    {

        return preg_replace('~_([a-z])~e', "strtoupper('\\1')", $string);
    }


    protected function _toUnderscore($string)
    {

        return preg_replace('~([A-Z])~e', "'_' . strtolower('\\1')", $string);
    }


    /**
     * @param Zend_Db_Select $select
     * @param array          $sort
     *
     * @return Core_Model
     */
    protected function _applySort(Zend_Db_Select $select, array $sort = array())
    {

        foreach ($sort as $key => $value) {
            $name     = ucfirst($this->_toCamelCase($key));
            $callback = "_applySort$name";
            if (method_exists($this, $callback)) {
                $this->$callback($select, $value);
            } else {
                $this->_applyDefaultSort($select, $name, $value);
            }
        }

        return $this;
    }


    protected function _applyDefaultSort(Zend_Db_Select $select, $name, $value)
    {

        $column = $this->_toUnderscore(lcfirst($name));
        if (!in_array($column, $this->_table->info(Zend_Db_Table::COLS))) {
            throw new Core_Model_Exception('Incorrect sort: ' . $name);
        }

        if ($value === 'desc') {
            $select->order("$this->_tableName.$column " . Zend_Db_Select::SQL_DESC);
        } elseif ($value === 'asc' || empty($value)) {
            $select->order("$this->_tableName.$column " . Zend_Db_Select::SQL_ASC);
        } else {
            throw new Core_Model_Exception('Incorrect sort value: ' . $value . '. Must be "asc" or "desc"');
        }
    }


    /**
     * @param Zend_Db_Select $select
     * @param int            $limit
     * @param int            $offset
     *
     * @return Core_Model
     */
    protected function _applyLimit(Zend_Db_Select $select, $limit = null, $offset = null)
    {

        if ($limit !== null || $offset !== null) {
            $select->limit($limit, $offset);
        }

        return $this;
    }
}
