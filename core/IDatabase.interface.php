<?php
/**
 * Author: Che-Ming Lin
 * E-mail: vinnie0921@gmail.com
 */
interface IDatabase
{
	public function connect($host, $user, $password, $dbname);
	
	public function closeConnection();
	
	public function getThreadId();
	
	public function getAssoc($result);
	
	public function select($sql);
	
	public function insert($sql);
	
	public function update($sql);
	
	public function delete($sql);
	
	public function escape($string);
}
?>