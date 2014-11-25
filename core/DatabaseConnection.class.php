<?php
/**
 * Author: Che-Ming Lin
 * E-mail: vinnie0921@gmail.com
 */

require_once('DatabaseEngineFactory.class.php');

class DatabaseConnection
{
    private $_engine;
    private $_errorMessage;

    function __destruct()
    {
    	
    }
    
    public function getErrorMessage()
    {
        return $this->_errorMessage;
    }

    public function openConnection($arrDsn)
    {
        $this->_engine = DatabaseEngineFactory::getEngine($arrDsn['phptype']);
        $this->_engine->connect($arrDsn['hostspec'], $arrDsn['username'], $arrDsn['password'], $arrDsn['database']);
        
        if($arrDsn['database'])
        {
            if(!$this->_engine->selectDatabase($arrDsn['database']))
            {
                $this->_errorMessage = $this->_engine->getErrorMessage();
                return false;
            }
        }

        return true;
    }

    public function closeConnection()
    {
        $this->_engine->closeConnection();
        unset($this->_engine);
		unset($this->_errorMessage);
    }

    public function getThreadId()
    {
        return $this->_engine->getThreadId();
    }

    public function select($sql)
    {
        return $this->_engine->select($sql);
    }

    public function update($sql)
    {
        return $this->_engine->update($sql);
    }

    public function insert($sql)
    {
        return $this->_engine->insert($sql);
    }

    public function delete($sql)
    {
        return $this->_engine->delete($sql);
    }
    
    public function replace($sql)
    {
    	return $this->_engine->replace($sql);
    }
    
    public function escape($string)
    {
    	return $this->_engine->escape($string);
    }
}
?>