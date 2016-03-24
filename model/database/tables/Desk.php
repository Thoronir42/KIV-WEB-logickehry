<?php

namespace model\database\tables;

/**
 * Description of User
 *
 * @author Stepan
 */
class Desk extends \model\database\DB_Entity {

	const NO_DESK = 0;
	
	/**
	 * 
	 * @param \PDO $pdo
	 * @return Desk
	 */
	public static function fetchAll($pdo) {
		$result = $pdo->query("SELECT * FROM desk")
				->fetchAll(\PDO::FETCH_CLASS, Desk::class);
		return $result;
	}

	var $desk_id;
	var $desk_number;
	var $capacity;

}
