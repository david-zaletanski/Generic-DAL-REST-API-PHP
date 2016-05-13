<?php
/**
 * Trackmini - REST API
 * TrackminiDBFactor.php
 * Contains database constants and provides static method of retrieving a new connection.
 * @Author: dzale
 * @Date:   2015-04-28 16:25:51
 * @Last Modified by:   dzale
 * @Last Modified time: 2015-05-12 03:13:22
 */

class TrackminiDBFactory
{
	/*
		Static/constant database connection variables.
	 */
	const DB_SERVER = "localhost";
	const DB_USERNAME = "root";
	const DB_PASSWORD = "goldhorse23";
	const DB_DATABASE = "trackmini_db";
	
	/**
	 * Returns a new connection to the Trackmini MySQL Database.
	 * @return mysql_connection	a connection to the database, or NULL if a connection cannot be made.
	 */
	public static function getConnection()
	{
		// Attempt to connect to the database.
		$con = mysqli_connect(self::DB_SERVER, self::DB_USERNAME, self::DB_PASSWORD, self::DB_DATABASE);

		// If we cannot connect return NULL to let caller know it failed.
		if (mysqli_connect_errno()) {
			return NULL;
		}
		return $con;
	}
}    

?>