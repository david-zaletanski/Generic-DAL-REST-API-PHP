<?php

// Create connection to MySQL database.
$con = mysqli_connect("localhost", "root", "goldhorse23", "trackmini_db");

// Check connection.
if (mysqli_connect_errno())
{
	echo "Failed to connect to MySQL: ".mysqli_connect_error();
}

// This SQL statement selects ALL from the table 'Items'
$sql = "SELECT * FROM item";

// Check if there are results.
if ($result = mysqli_query($con, $sql))
{
	// If so, create a results array and a temporary one.
	$resultArray = array();		// Stores all of the rows.
	$tempArray = array();		// Temporarily stores each row.

	// Loop through each row of the result set.
	while($row = $result->fetch_object())
	{
		// Add each row into our results array.
		$tempArray = $row;
		array_push($resultArray, $tempArray);
	}

	// Finally, encode the array to JSON and output the results.
	echo json_encode($resultArray);
}

// Close connections.
mysqli_close($con);

?>