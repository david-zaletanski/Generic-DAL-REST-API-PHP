<?php

/**
 *	Trackula - REST API
 *	api.php
 *	Desc: The code first called when a request comes in through the Trackmini API,
 *		redirect's the request through the API class.
 *	By: David Zaletanski
 *	Monday April 27th, 2015
 *	Original code: http://coreymaynard.com/blog/creating-a-restful-api-with-php/
 */

/**
 *	api.php
 *	The .htaccess file forwards all requests to this file, which handles the
 *	implementation of the API to handle requests.
 */
require dirname(__FILE__).'/TrackminiAPI.class.php';

/**
 * Optional: Turn error reporting on.
 */
//error_reporting(E_ALL);
//ini_set('display_errors',1);

/**
 * Option: Set PHP include directory.
 */
ini_set('include_path',"/var/www/html/trackmini/api");

// Requests from the same server don't have a HTTP_ORIGIN header
//  8-14-15 ? This is used for security? May not need.
if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
	$_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME']; // Set HTTP_ORIGIN to our server name.
}

try {
	// Create our API object, passing it the request and the origin.
	$API = new MyAPI($_REQUEST['request'], $_SERVER['HTTP_ORIGIN']);
	// Have the API process the request and return a result (JSON), then output it.
	echo $API->processAPI();
} catch (Exception $e) {
	// If there was an exception, output a JSON containing the exception message.
	echo json_encode(Array('error' => $e->getMessage()));
}

?>