
<?

abstract class TrackminiObject {
	public static $table_name = "";
	public $id;

	abstract public function Get($id);

	abstract public function Insert();

	abstract public function Update();

	abstract public function Delete();

}

?>