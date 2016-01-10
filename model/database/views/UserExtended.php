<?php

namespace model\database\views;

use \model\database\tables\User;

/**
 * Description of GamyTypeWithScore
 *
 * @author Stepan
 */
class UserExtended extends User {

	/**
	 * 
	 * @param \PDO $pdo
	 * @param String $orion_login
	 * @return UserExtended
	 */
	public static function fetch($pdo, $orion_login) {
		$statement = $pdo->prepare("SELECT * FROM user_extended
			WHERE orion_login = :ol");
		if ($statement->execute(['ol' => $orion_login])) {
			return $statement->fetchObject(UserExtended::class);
		}
		return null;
	}
	
	/**
	 * 
	 * @param \PDO $pdo
	 * @param int $user_id
	 * @return ReservationExtended
	 */
	public static function fetchById($pdo, $user_id) {
		$statement = $pdo->prepare("SELECT * FROM user_extended
			WHERE user_id = :id");
		if ($statement->execute(['id' => $user_id])) {
			return $statement->fetchObject(UserExtended::class);
		}
		return null;
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @return UserExtended[]
	 */
	public static function fetchAll($pdo) {
		$result = $pdo->query("SELECT * FROM user_extended")
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
