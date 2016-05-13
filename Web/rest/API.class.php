<?php

/**
 *	Trackula - REST API
 *	api.class.php
 *	Desc: Preprocesses the request and breaks it in to parts. Then attempts to
 *		process the request (delegating the actual processing) and returning an appropriate
 *		response.
 *	By: David Zaletanski
 *	Monday April 27th, 2015
 *	Original code: http://coreymaynard.com/blog/creating-a-restful-api-with-php/
 */

/**
 *	abstract class API
 *	An abstract implementation of a controller for REST service requests.
 * 	Used in conjunction with a .htaccess file accepts requests in the form:
 *	/<endpoint>/(optional)<verb>/(optional)<arg0>/(optional)<arg1>/...
 *	Note: Heavily commented as I dissect how this code works....
 */
abstract class API
{

	// ================================================================
	// 					API Properties
	// ================================================================

	/**
	 * 	Property: method
	 *	The HTTP method this request was made in: GET, POST, PUT, or DELETE
	 */
	protected $method = '';
	/**
	 *	Property: endpoint
	 *	The Model requested in the URI.	e.g. /files
	 */
	protected $endpoint = '';
	/**
	 *	Property: verb
	 *	An optional additional descriptor about the endpoint, used for things that
	 *	cannot be handled by the basic commands.	e.g. /files/process
	 */
	protected $verb = '';
	/**
	 *	Property: args
	 * 	Any additional URI components after the endpoint and verb have been removed, in our
	 *	case, and integer ID for the resource.	e.g. /<endpoint>/<verb>/<arg0>/<arg1>
	 */
	protected $args = Array();
	/**
	 *	Property: file
	 *	Stores the input of a PUT request.
	 */
	protected $file = Null;

	// ================================================================
	// 				Message Pre-Processing and Constructor
	// ================================================================

	/**
	 *	Constructor: __construct($request)
	 *	$request - Sent to our script from the .htaccess file containing the original URI the cilent
	 *		requested.
	 *	Allow for CORS, assemble and pre-process the data.
	 */
	public function __construct($request) {
		// 1. Set headers to enable CORS (Cross-Origin Resource Sharing) and allow
		// requests from any origin to be processed
		header("Access-Control-Allow-Origin: *");	// Allow request from any origin to be processed
		header("Access-Control-Allow-Methods: *");	// Allow any HTTP method to be accepted
		header("Content-Type: application/json");	// Set response content type.

		// 2. Clean up arguments which are formatted as a URI (endpoint/verb/arg0/arg1)

		// explode(delimiter,string[,int]) - returns an array of strings each of which
		//	is a substring of string formed by splitting it with a delimiter string
		$this->args = explode('/', rtrim($request, '/'));	// split URI into its string components
		// array_shift(array) - shifts the first value of the array off and returns it,
		//	shortening the array by one element and moving everything down (or NULL if empty)
		$this->endpoint = array_shift($this->args);		// remove the endpoint
		// array_key_exists(key,array) - returns TRUE if the given key is set in the array
		//	the key can be any value possible for an array index
		// is_numeric(var) - finds whether a variable is a number or a numeric string
		if (array_key_exists(0, $this->args) && !is_numeric($this->args[0])) {	// if there is anything after the endpoint and its not numeric
			$this->verb = array_shift($this->args);	// it must be a verb, pull that out
		}

		// 3. Check the method. Can be GET, but if it is POST can also be DELETE or PUT as found in the
		// 	HTTP_X_HTTP_METHOD header.
		$this->method = $_SERVER['REQUEST_METHOD'];
		if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
			// If it is POST and a HTTP_X_HTTP_METHOD header is set, check if it is DELETE or PUT
			if($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
				$this->method = 'DELETE';
			} else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
				$this->method = 'PUT';
			} else {
				// If not DELETE or PUT we don't know what it is...
				throw new Exception("Unexpected Header");
			}
		}

		// 4. Clean up our request data for safety, based on the request method.
		switch($this->method) {
			case 'DELETE':
				$this->request = $this->_cleanInputs($_POST);
				$this->file = file_get_contents("php://input");
				break;
			case 'POST':
				$this->request = $this->_cleanInputs($_POST);
				$this->file = file_get_contents("php://input");
				break;
			case 'GET':
				$this->request = $this->_cleanInputs($_GET);
				break;
			case 'PUT':
				$this->request = $this->_cleanInputs($_GET);
				// PUT requests put their data in a special file.
				$this->file = file_get_contents("php://input");
				break;
			default:
				// We don't know what kind of request it is, return an error.
				$this->_response('Invalid Method', 405);
				break;
		}

	} // End of __construct($request)

	// ================================================================
	// 						Message Processing
	// ================================================================

	/**
	 *	processAPI
	 *	Checks if the API contains a method with the same name of the endpoint
	 *	and sends a response containing the return value of that method provided the parsed args.
	 */
	public function processAPI() {
		// Check if our overall API class has a function for the endpoint.
		if ($this->checkEndpointForAPIFunction()) {
			// Send a response with the result of that method provided the parsed args
			//echo "processAPI: Found API function with name of endpoint. Calling API function.<BR />\n";
			return $this->_response($this->{$this->endpoint}($this->args)); 
		} else if ($this->checkEndpointForClass()) {
			// Check if a class exists with the name of the endpoint.
			//echo "processAPI: Found class exists. Creating instance and running instance method.<BR />\n";
			$r = new ReflectionClass($this->endpoint); // Create new instance.
			$classObject = $r->newInstance();
			return $this->processAPIClass($classObject); // Call instance processing functions.
		} else {
			// Try to process the request with generic algorithms.
			//echo "processAPI: Did not find existing class. Attempting generic method <BR />\n";
			$result = $this->processAPIGeneric();
			if(!is_null($result))	// If unable to process the request we get a null result.
				return $result;
		}
		// TODO: If method does not exist, look for an existing class and call its method.
		return $this->_response("No (API/Class/Generic) definition for endpoint: $this->endpoint", 404);
	}

	/**
	 * Processes a request using a given data model class object.
	 * @param  TrackminiModel $classObject A model class object containing an implementation to handle requests.
	 * @return string 	The result of the object's processing.
	 */
	protected function processAPIClass($classObject)
	{
		try {
			$result = '';
			switch ($this->method) { // Delegate processing to class object depending on request type.
				case 'GET':
					// Return
					$result = $classObject->get($this->args);
					break;
				case 'POST':
					// Create
					$result = $classObject->post($this->file);
					break;
				case 'PUT':
					// Update
					$result = $classObject->put($this->file);
					break;
				case 'DELETE':
					// Delete
					$result = $classObject->delete($this->file);
					break;
				default:
					// 405 Error
					$result = _response(_requestStatus(405), 405);
			}
			return $result;
		} catch (Exception $e) {
			echo 'Caught exception: '.$e->getMessage()."\n";
			return NULL;
		}
	}

	/**
	 * Attempts to process the request using generic functions.
	 * @return string The result of processing the request, or NULL if not possible.
	 */
	protected function processAPIGeneric()
	{
		try {
			$result = '';
			switch($this->method) {
				case 'GET':
					$result = $this->processGenericGET($this->args);
					break;
				case 'POST':
					$result = $this->processGenericPOST($this->file);
					break;
				case 'PUT':
					$result = $this->processGenericPUT($this->file);
					break;
				case 'DELETE':
					$result = $this->processGenericDELETE($this->file);
					break;
				default:
					$result = NULL;
			}
			return $result;
		} catch (Exception $e) {
			echo 'Caught exception: '.$e->getMessage()."\n";
			return NULL;
		}
	}

	/**
	 * Handles generic message processing for GET requests.
	 * @return string The response if successful, or NULL.
	 */
	protected abstract function processGenericGET();
	/**
	 * Handles generic message processing for POST requests.
	 * @return string The response if successful, or NULL.
	 */
	protected abstract function processGenericPOST();
	/**
	 * Handles generic message processing for PUT requests.
	 * @return string The response if successful, or NULL.
	 */
	protected abstract function processGenericPUT();
	/**
	 * Handles generic message processing for DELETE requests.
	 * @return string The response if successful, or NULL.
	 */
	protected abstract function processGenericDELETE();

	/**
	 * Checks if there is an API function for the called endpoint.
	 * @return boolean If there was an API function called.
	 */
	public function checkEndpointForAPIFunction() {
		// Checks if this object has a method for the endpoint.
		if((int)method_exists($this, $this->endpoint) > 0) {
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * Checks if a class exists in the namespace.
	 * @return boolean If the class exists in the namespace.
	 */
	public function checkEndpointForClass() {
		// Check if a class exists for the endpoint.
		$classFilename = dirname(__FILE__)."/Model/".$this->endpoint.".class.php";
		//echo 'checkEndpointForClass: $classFilename='.$classFilename." <BR />\n";
		include $classFilename;
		//if(class_exists($this->endpoint)) {
		// Attempt to autoload class and return if it exists (successful) or not.
		//if(class_exists($classFilename)) {
		if(class_exists($this->endpoint)) {
			return TRUE;
		}
		return FALSE;
	}

	/*function __autoload($class_name) {
		$fullClassName = $class_name.'.php';
		include $fullClassName;
		echo '__autoload: $fullClassName='.$fullClassName." <BR />\n";
		return class_exists($class_name, false);
	}*/

	// ====================== HELPER FUNCTIONS ==========================

	/**
	 *	_response($data, $status = 200)
	 *	Sends a JSON encoded response with the provided data. Assumes a status of 200 OK unless provided differently.
	 */
	private function _response($data, $status = 200) {
		header("HTTP/1.1 " . $status . $this->_requestStatus($status));
		return json_encode($data);
	}

	/**
	 *	_cleanInputs($data)
	 *	Input sanitizer. Trims and removes tags from data, recursively for arrays.
	 */
	private function _cleanInputs($data) {
		$clean_input = Array();
		if (is_array($data)) {
			foreach($data as $k => $v) {
				$clean_input[$k] = $this->_cleanInputs($v);
			}
		} else {
			// strip_tags(string str[, string allowable_tags])
			//	Strips HTML and PHP tags from a string, replacing them with NULL bytes.
			// trim(string str[, string character_masl])
			//	Strips whitespace (or other characters) from the beginning and end of a string.
			$clean_input = trim(strip_tags($data));
		}
		return $clean_input;
	}

	/**
	 *	_requestStatus($code)
	 *	Returns a string describing the numeric request status code.
	 */
	private function _requestStatus($code)
	{
		// Hard-coded status values.
		$status = array(
			200 => 'OK',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			500 => 'Internal Server Error'
		);
		// Checks if the hard coded value exists or returns an Internal Server Error for all other codes.
		return ($status[$code]) ? $status[$code] : $status[500];
	}

} // End of abstract class API

?>