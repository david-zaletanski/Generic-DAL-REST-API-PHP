<?php
/**
 * @Author: dzale
 * @Date:   2015-04-28 16:25:51
 * @Last Modified by:   dzale
 * @Last Modified time: 2015-05-01 23:43:57
 */

class TrackminiDBFactory
{
	/*
		Static/constant database connection variables.
	 */
	private static $_db_server = "localhost";
	private static $_db_username = "root";
	private static $_db_password = "goldhorse23";
	
	public static $db_database = "trackmini_db";
	
	/**
	 * Returns a new connection to the Trackmini MySQL Database.
	 * @return mysql_connection	a connection to the database, or NULL if a connection cannot be made.
	 */
	public static function getConnection()
	{
		// Attempt to connect to the database.
		$con = mysqli_connect(self::$_db_server, self::$_db_username, self::$_db_password, self::$db_database);

		// If we cannot connect return NULL to let caller know it failed.
		if (mysqli_connect_errno()) {
			return NULL;
		}
		return $con;
	}
}    

?>