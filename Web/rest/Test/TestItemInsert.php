<?php
/**
 * @Author: dzale
 * @Date:   2015-05-06 17:43:45
 * @Last Modified by:   dzale
 * @Last Modified time: 2015-05-12 03:02:34
 */

// ENABLE ERROR REPORTING
error_reporting(E_ALL);
ini_set('display_errors',1);

require '../Model/Item.class.php';

/**
 * Tests inserting an item.
 */

$item = new Item();
$item->id = 1;
$item->name = "Water Bottle";
$item->manufacturer = "Glaceua";
$item->description = "A tall bottle of water.";
$item->unit_quantity = 33.8;
$item->unit_measure = "FL OZ";

$item_string = json_encode($item);

$fields = array('item_json' => urlencode($item_string));
$fields_string = "";
foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
rtrim($fields_string, '&');

$ch = curl_init('http://trackula.me/trackmini/api/items');
curl_setopt($ch, CURLOPT_POST, count($fields));
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

$result = curl_exec($ch);
echo $result;
curl_close($ch);

?>