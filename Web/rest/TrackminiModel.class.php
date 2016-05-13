<?php
/**
 * Trackula - REST API
 * TrackminiModel.class.php
 * Desc: An abstract class representing a data model as stored in the 
 *  	Trackmini database. Manages creation of database connection and
 *  	JSON encoding, as well as abstract methods for request processing.
 * @Author: dzale
 * @Date:   2015-04-28 16:29:48
 * @Last Modified by:   dzale
 * @Last Modified time: 2015-05-12 03:56:57
 */

// TrackminiDBFactory is a class to access database connections.
require_once(dirname(__FILE__).'/TrackminiDBFactory.php');

/**
 * TrackminiModel
 * An super parent class for all Trackmini resource models.
 */
abstract class TrackminiModel {

	/** @var int A unique identifier for the model corresponding to a primary key in the database */
	//public $id;

	/** @var mysql_connection An open database connection, or NULL if not connected. */
	protected $con;

	/** @var string The name of the table for persisting this model. */
	private $model_table_name = '';

	/**
	 * Creates a TrackminiModel object responsible for database
	 * connection and interaction with a model's representational table.
	 * @param string $model_table_name the name of the model's table in the database.
	 */
	public function __construct($model_table_name) {
		// Store the name of the table for future reference.
		$this->model_table_name = $model_table_name;
		// Get a new database connection for use by the model.
		$this->con = TrackminiDBFactory::getConnection();
		if($this->con == NULL)
			exit('Could not create database connection in TrackminiModel:__construct');
	}
	/**
	 * Frees any resources held by the object, most notably the database connection.
	 */
	public function __destruct() {
		// Check if we have a database connection, and if so, closes it.
		if(!is_null($this->con)) {
			mysqli_close($this->con);
		}
	}
	/**
	 * Returns a JSON array of all objects from the model's table in the database.
	 */
	public function get()
	{
		// Create and execute our query, returning the results as a JSON object.
		$query = "SELECT * FROM ".TrackminiDBFactory::DB_DATABASE.".".$this->model_table_name;

		$resultArray = array();
		if ($result = mysqli_query($this->con, $query)) {
			$tempArray = array();

			// Loop through the query results and adding each item
			// to the result.
			while($row = $result->fetch_object()) {
				$tempArray = $row;
				array_push($resultArray, $tempArray);
			}
		}
		return json_encode($resultArray); // Empty array if none found.
	}

	/**
	 * Handles a POST request to insert model in to database.
	 * @return string The ID of the inserted row in the database.
	 */
	public abstract function post();
	/**
	 * Handles a PUT request to update the model in the database.
	 * @return string The number of affected rows.
	 */
	public abstract function put();
	/**
	 * Handles a DELETE request to remove the model from the database.
	 * @return string The number of affected rows.
	 */
	public abstract function delete();

	/**
	 * Determines if the attributes of this model are valid or not. Valid model's can be inserted in to the database.
	 * @return bool If all the model's attributes are valid.
	 */
	public abstract function validate();

	/**
	 * Converts this object in to a JSON string.
	 * @return the JSON object string 
	 */
	public function toJSON()
	{
		return json_encode($this);
	}

	/**
	 * A factory constructor for creating a new object from a JSON string.
	 * @param  string $json 	The object represented as a JSON string.
	 * @return TrackminiModel       A new object made from the JSON string.
	 */
	public static function getFromJSON($json)
	{
		$newclass = new TrackminiModel();
		$data = json_decode($json);
		foreach ($data as $key => $value) $newclass->{$key} = $value; // Automatically map properties to data.
		return $newclass;
	}
}

?>