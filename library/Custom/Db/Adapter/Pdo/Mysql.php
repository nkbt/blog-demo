<?php
/**
 * Class Custom_Db_Adapter_Pdo_Mysql
 */
class Custom_Db_Adapter_Pdo_Mysql extends Zend_Db_Adapter_Pdo_Mysql
{


    public $transactionStarted = false;


    /**
     * @return Custom_Db_Select
     */
    public function select()
    {

        return new Custom_Db_Select($this);
    }


    /**
     * @return Custom_Db_Adapter_Pdo_Mysql
     */
    public function beginTransaction()
    {

        if (!$this->transactionStarted) {
            $this->transactionStarted = true;

            return parent::beginTransaction();

        }

        return $this;
    }


    /**
     * @return Custom_Db_Adapter_Pdo_Mysql
     */
    public function commit()
    {

        if ($this->transactionStarted) {
            $this->transactionStarted = false;

            return parent::commit();
        }

        return $this;
    }


    /**
     * @return Custom_Db_Adapter_Pdo_Mysql
     */
    public function rollBack()
    {

        if ($this->transactionStarted) {
            $this->transactionStarted = false;

            return parent::rollBack();
        }

        return $this;
    }


}