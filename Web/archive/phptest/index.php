<?php
/**
 * @Author: dzale
 * @Date:   2015-04-30 01:49:43
 * @Last Modified by:   dzale
 * @Last Modified time: 2015-05-02 03:56:55
 */

// Import our classes.
require_once('./AParent.php');
require './TrackminiDBFactory.php'

//$aclass = new AParent();
echo 'Calling a classes static variables...';
echo "<br />\n";
echo AParent::APARENT_CONSTANT;
echo "<br />";
echo ' ' . AParent::$pub_stat_var . "\n";
echo AParent::statConstant();

$classA = new AParent();
echo $classA->showConstant() . "<br />";
$classA->statVarF();

$classA->avar = "sup";
echo $classA->avar . "<br/>";

$con = TrackminiDBFactory::getConnection();
if($con==NULL)
	echo "Connection is null.<br/>";
$query = "SELECT * FROM ".TrackminiDBFactory::$db_database.".".$classA->model_table_name;
echo $query."<br/>";
$resultArray = array();
if ($result = mysqli_query($con, $query)) {
	$tempArray = array();

			// Loop through the query results and adding each item
			// to the result.
	while($row = $result->fetch_object()) {
		$tempArray = $row;
		array_push($resultArray, $tempArray);
	}
}
echo json_encode($resultArray); // Empty array if none found.
?>