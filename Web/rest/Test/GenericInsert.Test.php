<?php
/**
 * @Author: dzale
 * @Date:   2015-05-12 01:44:04
 * @Last Modified by:   dzale
 * @Last Modified time: 2015-05-12 02:14:56
 */

// ENABLE ERROR REPORTING
error_reporting(E_ALL);
ini_set('display_errors',1);

/**
 * Tests inserting an object (user) through a generic request.
 */

$randNum = rand(1,10000); // Insert a new user with a random account #.
$obj = array( "login_username" => "test".(string)$randNum,
	"login_password" => "test123"
	);
$objFields = "";
foreach($obj as $key => $value) {
	$objFields .= urlencode($key).'='.urlencode($value).'&';
}
rtrim($objFields, '&');

$ch = curl_init('http://trackula.me/trackmini/api/user');
curl_setopt($ch, CURLOPT_POST, count($obj));
curl_setopt($ch, CURLOPT_POSTFIELDS, $objFields);

$result = curl_exec($ch);
echo $result;
curl_close($ch);

?>