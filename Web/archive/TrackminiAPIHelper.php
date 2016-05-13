<?php
// include 'otherfile.php'	just showing how

/* 	TrackminiAPI
	------------
	Manages connecting to the MySQL database and provides a few
	helper methods.
	http://www.gen-x-design.com/archives/create-a-rest-api-with-php/
*/
class TrackminiRestAPIHelper {
	// Holds our database connection.
	/*private $db;

	// Constructor - Open the database connection.
	function __construct() {
		$this->db = new mysqli('localhost','root','goldhorse23',
			'trackmini_db');
		$this->db->autocommit(FALSE);
	}
	// Destructor - Close the database connection.
	function __destruct() {
		$this->db->close();
	}*/

	/*
		processRequest()
			analyzes an incoming request from the client and creates
		a RestRequest object from it.
	*/ 
	public static function processRequest()
	{
		// Retrieve the lowercase version of request type (GET/POST/PUT)
		$request_method = strtolower($_SERVER['REQUEST_METHOD']);
		$return_obj = new RestRequest();
		$data = array();	// We'll store the request data here.

		// Depend on request type we use different methods to access data
		switch ($request_method)
		{
			// GET and POST requests are easy.
			case 'get':
				$data = $_GET;
				break;
			case 'post':
				$data = $_POST;
				break;
			// PUT requests require an extra step.
			case 'put':
				// 1. Read string from php's special input location.
				// 2. parse_str treats it like a query string from a URL.
				parse_str(file_get_contents('php://input'), $put_vars);
				$data = $put_vars;
				break;
		}

		// Store the method (request type).
		$return_obj->setMethod($request_method);

		// Store the raw data so we can access it if needed. (There may
		// be multiple other parts to your request than JSON)
		$return_obj->setRequestVars($data);

		// If the request has a data variable set we need to process it.
		if(isset($data['data']))
		{
			// Translate the JSON object to an Object for further use.
			$return_obj->setData(json_decode($data['data']));
		}
		return $return_obj;
	}

	// Sends a response to the client, with the body or status code error.
	public static function sendResponse($status = 200, $body = '', $content_type = 'text/html')
	{
		// Create the status header.
		$status_header = 'HTTP/1.1 ' . $status . ' ' . RestUtils::getStatusCodeMessage($status);
		// Set the header.
		header($status_header);
		// Set the content-type.
		header('Content-type: ' . $content_type);

		// Pages with body are easy.
		if($body != ' ')
		{
			// Send the body
			echo $body;
			exit;
		}
		else // Otherwise we need to create the body.
		{
			// Create body messages
			$message = '';

			// This part is purely optional, but makes the pages a little
			// nicer to read for users. Since you won't likely send a lot
			// of different status codes, this also shouldn't be too
			// ponderous to maintain
			switch($status)
			{
				case 401:
					$message = 'You must be authorized to view this page.';
					break;
				case 404:
					$message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
					break;
				case 500:
					$message = 'The server encountered an error processing your request.';
					break;
				case 501:
					$message = 'The request method is not implemented.';
					break;
			}

			// Servers don't always have a signature turned on.
			// This is an Apache directive 'ServerSignature On'
			$signature = ($_SERVER['SERVER_SIGNATURE'] == '') ?  $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];

			// this should be templatized in a real-world solution
			$body = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
						<html>
							<head>
								<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
								<title>' . $status . ' ' . RestUtils::getStatusCodeMessage($status) . '</title>
							</head>
							<body>
								<h1>' . RestUtils::getStatusCodeMessage($status) . '</h1>
								<p>' . $message . '</p>
								<hr />
								<address>' . $signature . '</address>
							</body>
						</html>';

			echo $body;
			exit;
		}
	}

	// Helper method to get a string description for an HTTP status code
	// From http://www.gen-x-design.com/archives/create-a-rest-api-with-php/ 
	function getStatusCodeMessage($status)
	{
    // these could be stored in a .ini file and loaded
    // via parse_ini_file()... however, this will suffice
    // for an example
		$codes = Array(
			100 => 'Continue',
			101 => 'Switching Protocols',
			200 => 'OK',
			201 => 'Created',
			202 => 'Accepted',
			203 => 'Non-Authoritative Information',
			204 => 'No Content',
			205 => 'Reset Content',
			206 => 'Partial Content',
			300 => 'Multiple Choices',
			301 => 'Moved Permanently',
			302 => 'Found',
			303 => 'See Other',
			304 => 'Not Modified',
			305 => 'Use Proxy',
			306 => '(Unused)',
			307 => 'Temporary Redirect',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			405 => 'Method Not Allowed',
			406 => 'Not Acceptable',
			407 => 'Proxy Authentication Required',
			408 => 'Request Timeout',
			409 => 'Conflict',
			410 => 'Gone',
			411 => 'Length Required',
			412 => 'Precondition Failed',
			413 => 'Request Entity Too Large',
			414 => 'Request-URI Too Long',
			415 => 'Unsupported Media Type',
			416 => 'Requested Range Not Satisfiable',
			417 => 'Expectation Failed',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			502 => 'Bad Gateway',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout',
			505 => 'HTTP Version Not Supported'
			);
		return (isset($codes[$status])) ? $codes[$status] : '';
	}

	// Helper method to send a HTTP response code/message
	function sendResponse($status = 200, $body = '', $content_type = 'text/html')
	{
		$status_header = 'HTTP/1.1 ' . $status . ' ' . getStatusCodeMessage($status);
		header($status_header);
		header('Content-type: ' . $content_type);
		echo $body;
	}

}

/*
	RestRequest
		We don't leave the house without taking.
*/
class RestRequest
{
	private $request_vars;
	private $data;
	private $http_accept;
	private $method;

	public function __construct()
	{
		$this->request_vars = array();
		$this->data 		= '';
		$this->http_accept  = (strpos($_SERVER['HTTP_ACCEPT'],
		  'json')) ? 'json' : 'xml';
		$this->method 		= 'get';
	}

	public function setData($data)
	{
		$this->data = $data;
	}
	public function setMethod($method)
	{
		$this->method = $method;
	}

	public function setRequestVars($request_vars)
	{
		$this->request_vars = $request_vars;
	}
	public function getData()
	{
		return $this->data;
	}
	public function getMethod()
	{
		return $this->method;
	}

	public function getHttpAccept()
	{
		return $this->http_accept;
	}
	public function getRequestVars()
	{
		return $this->request_vars;
	}
}



















?>