<?php
/**
 * Author: Che-Ming Lin
 * E-mail: vinnie0921@gmail.com
 */
require_once('MysqlDatabase.class.php');

class DatabaseEngineFactory
{
	public static function getEngine($str_database_engine)
	{
		switch ($str_database_engine)
		{
			case 'mysql':
				$objDatabaseEngine = new MysqlDatabase();
				return $objDatabaseEngine;
				break;
			case 'mssql':
				require_once('MssqlDatabase.class.php');
				$objDatabaseEngine = new MssqlDatabase();
				return $objDatabaseEngine;
				break;
			default:
				throw new Exception ('Database engine not found');
				break;
		}
	}
}
?>