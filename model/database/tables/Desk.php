<?php
namespace model\database\tables;

/**
 * Description of User
 *
 * @author Stepan
 */
class Desk extends \model\database\DB_Entity{
	
	public static function fetchAll($pw){
		$result = $pw->con->query("SELECT * FROM desk")
				->fetchAll(PDO::FETCH_CLASS, Desk::class);
		return $result;
	}
	
	var $desk_id;
	
	var $capacity;
	
}
