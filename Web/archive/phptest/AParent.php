<?php
/**
 * @Author: dzale
 * @Date:   2015-04-29 17:29:13
 * @Last Modified by:   dzale
 * @Last Modified time: 2015-05-02 03:55:33
 */

class AParent
{
	const APARENT_CONSTANT = 'This heres a constant!';
	public static $pub_stat_var = 'This is a public static variable.';
	private static $prv_stat_var = 'This is a private static variable.';

	public $model_name = 'item';

	/**
	 * A simple constructor.
	 */
	public function __construct() {
		
	}

	public function showConstant() {
		echo self::APARENT_CONSTANT;
	}

	public static function statConstant() {
		echo self::APARENT_CONSTANT;	
	}

	public static function statVarF() {
		echo self::$prv_stat_var . "<br />";
	}
}


class AChild extends AParent
{

	/**
	 * A simple constructor.
	 */
	public function __construct() {
		// Call parent constructor.
		parent::__construct();
	}

}

?>