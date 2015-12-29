<?php

namespace model\database\views;

use \model\database\tables\User;

/**
 * Description of GamyTypeWithScore
 *
 * @author Stepan
 */
class UserExtended extends User {

	public static function fetch($pw, $orion_login) {
		$statement = $pw->con->prepare("SELECT * FROM user_extended
			WHERE orion_login = :ol");
		if ($statement->execute(['ol' => $orion_login])) {
			return $statement->fetchObject(UserExtended::class);
		}
		return null;
	}
	
	public static function fetchAll($pw){
		$result = $pw->con->query("SELECT * FROM user_extended")
				->fetchAll(\PDO::FETCH_CLASS, UserExtended::class);
		return $result;
	}

	var $role_label;
	var $ratings;

	public function isSubscribedTo($id) {
		if (!$this->isLoggedIn() || !isset($this->subscribedGames)) {
			return false;
		}
		$subGames = $this->subscribedGames;
		$index = array_search($id, $subGames);
		return $index !== false;
	}

	public function setSubscribedItems($items) {
		$this->subscribedGames = $items;
	}

}
