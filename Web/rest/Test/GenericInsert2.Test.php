<?php
/**
 * @Author: dzale
 * @Date:   2015-05-20 16:24:11
 * @Last Modified by:   dzale
 * @Last Modified time: 2015-05-20 16:37:56
 */

include '../Model/Item.class.php';

// ENABLE ERROR REPORTING
error_reporting(E_ALL);
ini_set('display_errors',1);

/**
 * Tests inserting an object (item) through a generic request.
 */

$randNum = rand(1,10000); // Insert a new user with a random account #.
$item = new Item();
$item->id = 0;
$item->name = "Monster";
$item->manufacturer = "Monster Energy Company";
$item->description = "A delicious beverage that provides the energy needed for living through extreme carbonation, miscellaneous chemicals, and overly gratuitous amounts of sugar.";
$item->unit_quantity = 16.0;
$item->unit_measure = "FL OZ";
$item_string = json_encode($item);

$ch = curl_init('http://trackula.me/trackmini/api/item');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_POST, count($item_string));
curl_setopt($ch, CURLOPT_POSTFIELDS, $item_string);

$result = curl_exec($ch);
echo $result;
curl_close($ch);

?>