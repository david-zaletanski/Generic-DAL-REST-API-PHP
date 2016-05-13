<?php
/**
 * @Author: dzale
 * @Date:   2015-05-12 02:34:57
 * @Last Modified by:   dzale
 * @Last Modified time: 2015-05-12 02:35:37
 */

// ENABLE ERROR REPORTING
error_reporting(E_ALL);
ini_set('display_errors',1);

/**
 * Tests updating an object (user) through a generic request.
 */

$obj = array( "id" => "7",
	"login_username" => "test",
	"login_password" => "test123456"
	);
$objString = json_encode($obj);

$ch = curl_init('http://trackula.me/trackmini/api/user');
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_POSTFIELDS, $objString);

$result = curl_exec($ch);
echo $result;
curl_close($ch);

?>