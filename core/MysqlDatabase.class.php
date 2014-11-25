<?php
/**
 * Author: Che-Ming Lin
 * E-mail: vinnie0921@gmail.com
 */
include_once("IDatabase.interface.php");

final class MysqlDatabase implements IDatabase
{
	private $_errorMessage;
	protected $_connection = null;

	public function connect($host, $user, $password, $dbname)
	{
		if ($this->_connection) 
		{
            return;
        }
        
        $this->_connection = mysqli_init();
        
        $isConnected = @mysqli_real_connect(
            $this->_connection,
            $host,
            $user,
            $password,
            $dbname,
            null,
            null,
            MYSQLI_CLIENT_COMPRESS
        );

		if ($isConnected === false || mysqli_connect_errno()) 
		{

            $this->closeConnection();
            throw new Exception(mysqli_connect_error());
        }
        
        mysqli_set_charset($this->_connection, 'UTF8');
	}

	/*private function checkLink($link)
	{
		if(ini_get('mysqli.reconnect') == '0')
		{
			ini_set('mysqli.reconnect', '1');
		}

		if (!mysqli_ping($link))
		{
			if(is_object($link))
			{
				mysqli_close($link);
			}

			throw new Exception('Mysql timeout!');
			//$link = $this->reconnect();
		}

		return $link;
	}*/

	/**
     * Test if a connection is active
     *
     * @return boolean
     */
    public function isConnected()
    {
        return ((bool) ($this->_connection instanceof mysqli));
    }

    /**
     * Force the connection to close.
     *
     * @return void
     */
    public function closeConnection()
    {
        if ($this->isConnected()) 
        {
            $this->_connection->close();
        }
        $this->_connection = null;
    }

	public function getAssoc($result)
	{
		try 
		{
			$i = 0;
			$arrRes = array();
			if(memory_get_usage(true) > 10485760)
			{
				$log = 'TIME:' . $_SERVER['REQUEST_TIME'] . "\n";
				$log.= 'IP:' . $_SERVER['REMOTE_ADDR'] . "\n";
				$log.= 'METHOD:' . $_SERVER['REQUEST_METHOD'] . "\n";
				$log.= 'HTTP REFERER:' . $_SERVER['HTTP_REFERER'] . "\n";
				$log.= 'REQUEST_URI:' . $_SERVER['REQUEST_URI'] . "\n";
				$log.= 'memory:' . memory_get_usage(true) . "\n\n";
				file_put_contents('/tmp/db_error.log', $log, FILE_APPEND);
			}
			//if(memory_get_usage(true) > 16777216)
			
			while ($arrTmp = $result->fetch_assoc())
			{
				$arrRes[$i] = $arrTmp;
				++$i;
			
				unset($arrTmp);
			}
			
			$result->free();
			
			return $arrRes;
		} 
		catch (Exception $e) 
		{
			$log = 'HTTP REFERER:' . $_SERVER['HTTP_REFERER'] . "\n";
			$log.= 'Caught exception: ' .  $e->getMessage() . "\n";
			$log.= 'memory:' . memory_get_usage(true) . "\n\n";
			file_put_contents('/tmp/db_error.log', $log, FILE_APPEND);
			//error_log($log, '/tmp/db_error.log');
    		echo 'Caught exception: ',  $e->getMessage(), "\n";
    		die('mysql error');
		}
	}
	
	public function getErrorMessage()
	{
		return $this->_connection->error;
	}

	public function getThreadId()
	{
		return $this->_connection->thread_id;
	}

	public function selectDatabase($dbname)
	{
		return $this->_connection->select_db($dbname);
	}

	public function select($sql)
	{
		$result = $this->_connection->query($sql);

		if(!$result)
		{
			$this->_errorMessage = $this->_connection->error;
			throw new Exception($this->_errorMessage);
		}

		$array = self::getAssoc($result);

		return $array;
	}

	public function insert($sql)
	{
		$result = $this->_connection->query($sql);
		$insertId = $this->_connection->insert_id;

		if(!$result)
		{
			$this->_errorMessage = $this->_connection->error;
			throw new Exception($this->_errorMessage);
		}

		return $insertId;
	}

	public function update($sql)
	{
		$result = $this->_connection->query($sql);

		if(!$result)
		{
			$this->_errorMessage = $this->_connection->error;
			throw new Exception($this->_errorMessage);
		}

		return $this->_connection->affected_rows;
	}

	public function delete($sql)
	{
		$result = $this->_connection->query($sql);

		if(!$result)
		{
			$this->_errorMessage = $this->_connection->error;
			throw new Exception($this->_errorMessage);
		}

		return $this->_connection->affected_rows;
	}
	
	public function replace($sql)
	{
		$result = $this->_connection->query($sql);

		if(!$result)
		{
			$this->_errorMessage = $this->_connection->error;
			throw new Exception($this->_errorMessage);
		}

		return $this->_connection->affected_rows;
	}

	public function escape($string)
	{
		if(is_array($string))
		{
			throw new Exception("You can't use this method to escape an Array! ");
		}
		
		return $this->_connection->real_escape_string($string);
	}	
}
?>