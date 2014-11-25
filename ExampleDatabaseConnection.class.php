<?php
/**
 * Author: Che-Ming Lin
 * E-mail: vinnie0921@gmail.com
 */
require_once("core/database_connection.class.php");

class ExampleDatabaseConnection extends DatabaseConnection 
{
    private $_debug = true;
    private static $instance;
    
	private function __construct()
	{
	    $arrDsn = array(
	       'phptype' => EXAMPLE_DATABASE_PHPTYPE,
	       'username' => EXAMPLE_DATABASE_USERNAME,
	       'password' => EXAMPLE_DATABASE_PASSWORD,
	       'hostspec' => EXAMPLE_DATABASE_HOSTSPEC,
	       'database' => EXAMPLE_DATABASE_DATABASE);

		if(!$this->openConnection($arrDsn))
		{
		    echo $this->getErrorMessage();
		}
	}
	
	function __destruct()
	{
		$this->closeConnection();
	}
	
	public static function getInstance()
	{
		if (!isset(self::$instance)) 
		{
            $c = __CLASS__;
            self::$instance = new $c;
        }

        return self::$instance;
	}
}
?>