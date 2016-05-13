<?php

/**
 *	Trackula - REST API Models
 *	Item.class.php
 *	Desc: A data model class representing an Item in the database. Contains
 *	 	implementation of data and processing functions.
 *	By: David Zaletanski
 *	Tuesday April 28th, 2015
 */

//require_once('../TrackminiModel.class.php');
//require_once('../TrackminiDBFactory.php');
// dirname(__FILE__).'
require_once(dirname(__FILE__).'/../TrackminiModel.class.php');
require_once(dirname(__FILE__).'/../TrackminiDBFactory.php');

class Item extends TrackminiModel {
	
	/** The name of the MySQL table backing this model. */
	//private static $_TABLE_NAME = 'item';
	const TABLE_NAME = "item";

	public $id = 0;
	public $name = '';
	public $manufacturer = '';
	public $description = '';
	public $unit_quantity = 1.0;
	public $unit_measure = 'Units';

	/**
	 * Creates an Items model used for interaction between the API and database.
	 */
	public function __construct() {
		// Parent constructor manages database connection.
		parent::__construct(self::TABLE_NAME);
	}

	/**
	 * Factory constructor for an item provided the JSON representation object.
	 * @param  string $json A JSON item object string.
	 * @return item 		A new item object.
	 */
	public static function getFromJSON($json) {
		$newitem = new Item();

		$data = json_decode($json); // Decode JSON
		foreach ($data as $key => $value) $newitem->{$key} = $value; // Automatically map properties to data.

		return $newitem;
	}
	
	/**
	 * Returns the item with the specified id. Variadic function, but only supposed to have 1 parameter.
	 * @param  $id The index (key) of the item to get.
	 * @return item The item object for a given key.
	 */
	public function get() {
		// Variadic function which takes in a variable number of arguments to get 'id'
		$id = 0;
		// Variadic function = takes 0 or more parameters.
		if(func_num_args()>0) { // Check if we have parameters.
			// If we have at least 1 parameter, the argument list is an array at index 0.
			$args = func_get_arg(0);
			// If we have at least 1 argument, it is the id at index 0.
			if(count($args)>=1) {
				$id = intval($args[0]); // Get int value from string
				if ($id <= 0) { // Check if id is valid (>=1)
					return json_encode(array('Error' => 'Invalid id.'));
				}
			}
		}
		if($id<=0) {
			// There is either no arguments provided, or it was an invalid ID. Call TrackminiModel get()
			return parent::get();
			//return json_encode(array('Error' => "Did not provide an ID for item: '{$id}'"));
		}

		// Create our query and empty arrays to store results.
		$query = "SELECT * FROM ".TrackminiDBFactory::DB_DATABASE.".".self::TABLE_NAME." WHERE id = ".$id;;

		$resultArray = array();
		if($result = mysqli_query($this->con, $query)) {
			$tempArray = array();

			// Loop through query result row, adding each item to the
			// complete result array.
			while($row = $result->fetch_object()) {	// While still returning results
				$tempArray = $row;					// keep fetching rows
				array_push($resultArray, $tempArray); //Add temporary row to results
			}
		}
		return json_encode($resultArray);
	}

	/**
	 * Creates a new item contained in the post data.
	 * @return item the newly created item.
	 */
	public function post()
	{
		$newitem = NULL;
		if(func_num_args()>0) { // Check if we have parameters.
			// If we have at least 1 parameter, the argument list is an array at index 0.
			$args = func_get_arg(0);
			
			// If we have at least 1 argument, it is the id at index 0.
			if(count($args)>=1) {
				$item_json = $args["item_json"];
				$newitem = Item::getFromJSON($item_json);
			}
		}

		if (is_null($newitem)) {
			return json_encode(array('Error' => 'Unable to parse Item JSON.'));
		}

		$query = "INSERT INTO ".TrackminiDBFactory::DB_DATABASE.".".self::TABLE_NAME." (name,manufacturer,description,unit_quantity,unit_measure) VALUES ('".$newitem->name."','".$newitem->manufacturer."','".$newitem->description."',".$newitem->unit_quantity.",'".$newitem->unit_measure."')";
		mysqli_query($this->con, $query) or die(mysqli_error($this->con));
		$newitem->id = mysqli_insert_id($this->con);

		return json_encode($newitem);
	}

	/**
	 * Updates the item contained in the PUT data file.
	 * @return array An array containing the 'affected_rows' as a result of the query.
	 */
	public function put()
	{
		$newitem = NULL;
		if(func_num_args()>0) { // Check if we have parameters.
			// Put request gives the item JSON string as argument.
			$args = func_get_arg(0);
			
			if(!is_null($args)) {
				$newitem = Item::getFromJSON($args);
			}
		}
		if (is_null($newitem)) {
			return json_encode(array('Error' => 'Unable to parse Item JSON.'));
		}

		$query = "UPDATE ".TrackminiDBFactory::DB_DATABASE.".".self::TABLE_NAME." SET name='".$newitem->name."', manufacturer='".$newitem->manufacturer."', description='".$newitem->description."', unit_quantity=".$newitem->unit_quantity.", unit_measure='".$newitem->unit_measure."' WHERE id=".$newitem->id;
		mysqli_query($this->con, $query) or die(mysqli_error($this->con));
		$result = mysqli_affected_rows($this->con);
		$resultarr = array('affected_rows' => $result);
		//$newitem->id = mysqli_insert_id($this->con);

		return json_encode($resultarr);
	}

	/**
	 * Deletes the item containing in the PUT data file.
	 * @return array An array containing the 'affected_rows' as a result of the query.
	 */
	public function delete()
	{
		$newitem = NULL;
		if(func_num_args()>0) { // Check if we have parameters.
			// Put request gives the item JSON string as argument.
			$args = func_get_arg(0);
			
			if(!is_null($args)) {
				$newitem = Item::getFromJSON($args);
			}
		}
		if (is_null($newitem)) {
			return json_encode(array('Error' => 'Unable to parse Item JSON.'));
		}

		$query = "DELETE FROM ".TrackminiDBFactory::DB_DATABASE.".".self::TABLE_NAME." WHERE id=".$newitem->id;
		mysqli_query($this->con, $query) or die(mysqli_error($this->con));
		$result = mysqli_affected_rows($this->con);
		$resultarr = array('affected_rows' => $result);
		//$newitem->id = mysqli_insert_id($this->con);

		return json_encode($resultarr);
	}

	/**
	 * Returns true or false depending on if the fields for this Item are valid.
	 * Valid fields mean the model can be inserted in to the database.
	 * @return bool if the fields for this Item are valid.
	 */
	public function validate()
	{
		// Assume it is valid and check for any condition with one of its fields
		// that would make it invalid.
		$valid = true;

		// $id should be 1 or higher.
		if ($id <= 0)
			$valid = false;
		// $name should not be empty and a maximum of 255 chars.
		if ($name == '' || strlen($name) > 255)
			$valid = false;
		// $manufacturer has a maximum of 50 chars.
		if (strlen($manufacturer) > 50)
			$valid = false;
		// $description should not be empty and a maximum of 255 chars.
		if ($description == '' || strlen($description) > 255)
			$valid = false;
		// $unit_quantity should be a decimal with a max of 10 digits, with 4 to the right of decimal point.
		if ($unit_quantity >= 0) {
			$unit_quantity_a = explode(".", $unit_quantity);
			$unit_quantity_l = strlen($unit_quantity_a[0]);
			$unit_quantity_r = strlen($unit_quantity_a[1]);
			if ($unit_quantity_l + $unit_quantity_r > 10 || $unit_quantity_r > 4)
				$valid=false;
		}
		// $unit_measure should not be empty and a maximum of 25 chars.
		if ($unit_measure == '' || strlen($unit_measure) > 25)
			$valid = false;

		return $valid;
	}
}

?>