<?php

/**
 *	Trackula - REST API
 *	MyAPI.class.php
 *	Desc: An implementation of the RestAPI abstract class. Contains endpoint API functions
 *		and generic request processing functions.
 *	By: David Zaletanski
 *	Monday April 27th, 2015
 *	Original code: http://coreymaynard.com/blog/creating-a-restful-api-with-php/
 */

// Import our abstract API REST controller class.
require_once(dirname(__FILE__).'/API.class.php');
//require_once('./Model/Item.class.php');
require_once(dirname(__FILE__).'/TrackminiDBFactory.php');

class MyAPI extends API
{

	/**
	 * The TrackminiAPI class constructor.
	 * @param HttpRequest $request An incoming request to the Trackmini REST API.
	 * @param string $origin  The origin of the request.
	 */
	public function __construct($request, $origin) {
		// Call the abstract constructor to break down the request type and clean parameters.
		parent::__construct($request);
		// TODO: Add security to prevent unauthorized access. This can be accomplished by
		// having an API key that must be included with the request. An invalid key is denied.
	}

	// ================================================================
	// 					Generic Request Processing
	// ================================================================

	/**
	 * Attempts to process a GET request using generic functionality.
	 * @return string The result of processing the request or NULL if processing not possible.
	 */
	protected function processGenericGET()
	{
		$result = NULL;

		// Get the ID in arguments of GET request.
		$id = 0;
		if(func_num_args()>0) {	// Get ID if supplied as an argument.
			$args = func_get_arg(0);
			//var_dump($args);
			if(count($args)>=1 && is_numeric($args[0])) {
				$id = intval($args[0]);
			}
		}

		// Create the SELECT query.
		$con = TrackminiDBFactory::getConnection(); // Get a database connection.
		$tableName = $this->endpoint;
		$query = NULL;
		// Pick query: Select single object with id, or select all objects.
		if($id > 0) {
			// SELECT single object with a specific id.
			$query = "SELECT * FROM ".TrackminiDBFactory::DB_DATABASE.".".$tableName." WHERE id = ".$id;
		} else {
			// Select all objects.
			$query = "SELECT * FROM ".TrackminiDBFactory::DB_DATABASE.".".$tableName;
		}

		// Return the result with a list of objects (1 to many items) or error (error)
		$resultArray = array();
		if($result = mysqli_query($con, $query)) {
			$tempArray = array();
			while ($row = $result->fetch_object()) {
				$tempArray = $row;
				array_push($resultArray, $tempArray);
			}
		} else {
			return json_encode(array("error" => mysqli_error($con)));
		}
		return json_encode($resultArray, JSON_NUMERIC_CHECK);
	}

	/**
	 * Attempts to process a POST request using generic functionality.
	 * NOTE: This function takes input as URL parameters (similar to a GET request). Use
	 * processGenericPOSTN() to take a JSON object as the parameter (request data stored in php://input file)
	 * @return string The result of processing the request or NULL if processing not possible.
	 */
	/*protected function processGenericPOST()
	{
		$result = NULL;

		// Get the ID in arguments of GET request.
		$args = array();
		if(func_num_args()>0) {	// Get ID if supplied as an argument.
			$args = func_get_arg(0);
		}
		if (count($args)==0)
			return json_encode(array("error" => "Object data not provided for POST request."));

		// Create the INSERT query.
		$con = TrackminiDBFactory::getConnection(); // Get a database connection.
		$tableName = $this->endpoint;
		$query = "INSERT INTO ".TrackminiDBFactory::DB_DATABASE.".".$tableName." (";
		$keys = array_keys($args);
		foreach($keys as $key) {	// Create a list of field names.
			$query = $query.$key.", ";
		}
		$query = substr($query, 0, -2); // Remove last comma.
		$query = $query.") VALUES (";
		foreach($keys as $key) {	// Create a list of values.
			$prop = $args[$key];
			if (is_string($prop)) { // Surround value in quotes if string.
				$query = $query."'".$prop."'".", ";
			} else {
				$query = $query.$prop.", ";
			}
		}
		$query = substr($query, 0, -2); // Remove last comma.
		$query = $query.")";

		// Return the result of the query (id) or error (error).
		$result = mysqli_query($con, $query);
		if(!$result) {
			return json_encode(array("error" => mysqli_error($con)));
		} else {
			$id = mysqli_insert_id($con);
			$id_array = array("id" => $id);

			return json_encode($id_array, JSON_NUMERIC_CHECK);
		}
	}*/

	/**
	 * Attempts to process a POST request using generic functionality.
	 * @return string The result of processing the request or NULL if processing not possible.
	 */
	protected function processGenericPOST()
	{
		$result = NULL;

		// Get the object data to insert from POST request.
		$args = array();
		if(func_num_args()>0) {
			$argsFile = func_get_arg(0);	// Model passed in php://input file.
			$args = json_decode($argsFile);
		}
		if(count($args)==0 || !isset($args->id))
			return json_encode(array("error" => "id not provided for POST request."));

		$con = TrackminiDBFactory::getConnection(); // Get database connection.
		$tableName = $this->endpoint;
		$query = "INSERT INTO ".TrackminiDBFactory::DB_DATABASE.".".$tableName." (";
		foreach($args as $key => $value) {
			$query .= $key . ",";
		}
		$query = substr($query, 0, -1); // Remove last comma from query.
		$query .= ") VALUES (";
		foreach($args as $key => $value) {
			if (is_string($value)) {
				$query = $query."'".$value."'".", ";
			} else {
				$query = $query.$value.", ";
			}
		}
		$query = substr($query, 0, -2); // Remove last command and space from query.
		$query .= ")";

		// Return the result of the query (id) or error (error).
		$result = mysqli_query($con, $query);
		if(!$result) {
			return json_encode(array("error" => mysqli_error($con)));
		} else {
			$id = mysqli_insert_id($con);
			//$id_array = array("id" => $id);
			//return json_encode($id_array, JSON_NUMERIC_CHECK);
			
			// Return the inserted object with the updated id.
			$args->id = $id;
			return json_encode($args, JSON_NUMERIC_CHECK);
		}

		return $result;
	}

	/**
	 * Attempts to process a PUT request using generic functionality.
	 * @return string The result of processing the request or NULL if processing not possible.
	 */
	protected function processGenericPUT()
	{
		$result = NULL;

		// Get the object data to update from PUT request.
		$args = array();
		if(func_num_args()>0) {
			$argsFile = func_get_arg(0);
			$args = json_decode($argsFile);
		}
		if (count($args)==0 || !isset($args->id))
			return json_encode(array("error" => "id not provided for PUT request."));

		// Create the UDPATE query.
		$con = TrackminiDBFactory::getConnection(); // Get a database connection.
		$tableName = $this->endpoint;
		$query = "UPDATE ".TrackminiDBFactory::DB_DATABASE.".".$tableName." SET ";
		foreach($args as $key => $value) { // Compose query with list of value names and new values.
			if ($key == "id") // Skip ID until later in query WHERE clause.
				continue;
			if (is_string($value)) { // Surround value in quotes if string.
				$query .= $key." = '".$value."'".", ";
			} else {
				$query .= $key." = ".$prop.", ";
			}
		}
		$query = substr($query, 0, -2); // Remove last comma from list.
		$query = $query." WHERE id = ".$args->id;

		// Return result of query (affected_rows) or error (error).
		$result = mysqli_query($con, $query);
		if(!$result) {
			return json_encode(array("error" => mysqli_error($con)));
		} else {
			$affectedRows = mysqli_affected_rows($con);
			$affectedRows_array = array("affected_rows" => $affectedRows);

			return json_encode($affectedRows_array, JSON_NUMERIC_CHECK);
		}
	}

	/**
	 * Attempts to process a DELETE request using generic functionality.
	 * @return string The result of processing the request or NULL if processing not possible.
	 */
	protected function processGenericDELETE()
	{
		$result = NULL;

		// Get the object data to delete from DELETE request.
		$args = array();
		if(func_num_args()>0) { 
			$argsFile = func_get_arg(0);
			$args = json_decode($argsFile);
		}
		if (count($args)==0 || !isset($args->id)) // To DELETE an object it must have an ID.
			return json_encode(array("error" => "id not provided for DELETE request."));

		// Create the DELETE query.
		$con = TrackminiDBFactory::getConnection(); // Get a database connection.
		$tableName = $this->endpoint;
		$query = "DELETE FROM ".TrackminiDBFactory::DB_DATABASE.".".$tableName;
		$query .= " WHERE id = ".$args->id;

		// Return the result of query (affected_rows) or the error (error).
		$result = mysqli_query($con, $query);
		if(!$result) {
			return json_encode(array("error" => mysqli_error($con)));
		} else {
			$affectedRows = mysqli_affected_rows($con);
			$affectedRows_array = array("affected_rows" => $affectedRows);

			return json_encode($affectedRows_array, JSON_NUMERIC_CHECK);
		}
	}

	// ================================================================
	// 						API Function Processing
	// ================================================================

	/* ======================= API TEST FUNCTIONS ======================= */

	/**
	 * 	Endpoint: ping
	 *	Returns a simply yet effective message, "PONG".
	 */
	protected function ping() {
		return "PONG";
	}

	/**
	 * 	Endpoint: showArgs
	 *	Returns the array of arguments provided to the resource.
	 */
	protected function showArgs($args) {
		return $args;
	}

	/* ======================= API  FUNCTIONS ======================= */

	/**
	 * 	Endpoint: items
	 *	Handles requests for creation, updating, and deletion of Item objects.
	 */
	
	/**
	 * Endpoint: items
	 * Handles CRUD requests for item objects.
	 * @param  array $args The array of URL parameters provided.
	 * @return object       The result of operations (response). 
	 */
	/*protected function items($args) {
		$result = '';
		switch ($this->method) {
			case 'GET':
				// return
				$item = new Item();
				$result = $item->get($args);
				break;
			case 'POST':
				// create
				$item = new Item();
				$result = $item->post($this->request);
				break;
			case 'PUT':
				// update
				$item = new Item();
				$result = $item->put($this->file);
				break;
			case 'DELETE':
				// delete
				$item = new Item();
				$result = $item->delete($this->file);
				break;
			default:
				// 405 error
				
		}
		return $result;
	}*/

}

?>