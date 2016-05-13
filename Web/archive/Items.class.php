<?php

/**
 *	Trackula - REST API Models
 *	Item.class.php
 *	By: David Zaletanski
 *	Tuesday April 28th, 2015
 */

class Item extends TrackminiModel {
	
	/** The name of the MySQL table backing this model. */
	const MYSQL_TABLE_NAME = 'item';

	public $id = 0;
	public $name = '';
	public $manufacturer = '';
	public $description = '';
	public $unit_quantity = 1.0;
	public $unit_measure = 'Units';

	/**
	 * Creates an Items model used for interaction between the API and database.
	 */
	public function __construct($request) {
		// Parent constructor manages database connection.
		parent::__construct(MYSQL_TABLE_NAME);
	}

	/**
	 *	get($id)
	 *	Returns an item from the database based on the provided ID.
	 */
	
	/**
	 * Returns the item with the specified id.
	 * @param  int $id The unique identifier (primary key) of the item.
	 * @return item The item object for a given key.
	 */
	public function get($id) {
		// Create our query and empty arrays to store results.
		$query = "SELECT * FROM ".TrackminiDBFactory::$db_database.".".MYSQL_TABLE_NAME."WHERE id = $id";
		$resultArray = array();
		if($result = mysqli_query($con, $query)) {
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