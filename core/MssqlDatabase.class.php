<?php
/**
 * Author: Che-Ming Lin
 * E-mail: vinnie0921@gmail.com
 * 
 * FreeBSD: /usr/local/etc/freetds.conf
 */
require_once("IDatabase.interface.php");

final class MssqlDatabase implements IDatabase
{
	private static $instance;
	private $_errorMessage;

	private function __construct(){}

	public static function get_instance()
	{
		if (!isset(self::$instance))
		{
			$c = __CLASS__;
			self::$instance = new $c;
		}

		return self::$instance;
	}

	public function connect($host, $user, $password, $dbname=null)
	{
		$link = mssql_connect($host, $user, $password, $dbname);
		mssql_query("set ANSI_NULLS on", $link);
		mssql_query("set ANSI_WARNINGS on", $link);

		if(!$link)
		{
			die("MS-SQL connection failed!");
		}

		return $link;
	}

	public function closeConnection($link)
	{
		return mssql_close($link);
	}

	public function getErrorMessage($link)
	{
		return mssql_get_last_message();
	}

	public function getThreadId($link)
	{
		// ms sql is not support
		return 0;
	}

	public function selectDatabase($link, $dbname)
	{
		return mssql_select_db($dbname, $link);
	}

	public function getAssoc($result)
	{
		$i = 0;
		$arrRes = array();
		while ($arrTmp = mssql_fetch_assoc($result))
		{
			$arrRes[$i] = $arrTmp;
			++$i;
		}

		return $arrRes;
	}

	public function select($link, $sql)
	{
		$result = mssql_query($sql, $link);

		if(!$result)
		{
			$this->_errorMessage = self::getErrorMessage($link);
			throw new Exception($this->_errorMessage);
		}

		$array = self::getAssoc($result);
		mssql_free_result($result);

		return $array;
	}

	public function insert($link, $sql)
	{
		mssql_query($sql, $link);
		$sql = " SELECT @@IDENTITY AS insert_id ";
		$result = mssql_query($sql);
		$row = mssql_fetch_object($result);

		return $row->insert_id;
	}

	public function update($link, $sql)
	{
		mssql_query($sql, $link);

		return mssql_affected_rows($link);
	}

	public function delete($link, $sql)
	{
		mssql_query($sql, $link);

		return mssql_affected_rows($link);
	}

	public function escape($link, $string)
    {
    	return addslashes($string);
    }
}
?>