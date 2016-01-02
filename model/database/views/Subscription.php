<?php

namespace model\database\views;

/**
 * Description of Subscription
 *
 * @author Stepan
 */
class Subscription {

	/**
	 * 
	 * @param \PDO $pdo
	 * @param int $user_id
	 * @return int[]
	 */
	public static function fetchGamesByUser($pdo, $user_id) {
		$statement = $pdo->prepare("SELECT game_type_id FROM subscription
			WHERE user_id = :uid");
		if ($statement->execute(['uid' => $user_id])) {
			return $statement->fetchAll(\PDO::FETCH_COLUMN);
		}
		return null;
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param int $game_type_id
	 * @return int[]
	 */
	public static function fetchUsersByGame($pdo, $game_type_id) {
		$statement = $pdo->prepare("SELECT orion_login FROM subscribees "
				. "WHERE game_type_id = :gid");
		if ($statement->execute(['gid' => $game_type_id])) {
			return $statement->fetchAll(\PDO::FETCH_COLUMN);
		}
		return null;
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param int $user_id
	 * @return int[]
	 */
	public static function fetchGamesDetailedByUser($pdo, $user_id) {
		$statement = $pdo->prepare("SELECT game_type_extended.* FROM game_type_extended "
				. "JOIN subscription ON subscription.game_type_id = game_type_extended.game_type_id "
				. "WHERE subscription.user_id = :uid");
		if ($statement->execute(['uid' => $user_id])) {
			return $statement->fetchAll(\PDO::FETCH_CLASS, GameTypeExtended::class);
		}
		return null;
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param int $user_id
	 * @param int $game_type_id
	 * @return boolean
	 */
	public static function remove($pdo, $user_id, $game_type_id) {
		$statement = $pdo->prepare("DELETE FROM subscription "
				. "WHERE user_id = :uid AND game_type_id = :gid");
		if ($statement->execute(['uid' => $user_id, 'gid' => $game_type_id])) {
			return true;
		} else {
			var_dump($statement->errorInfo());
		}
	}

	/**
	 * @param \PDO $pdo
	 * @param int $user_id
	 * @param int $game_type_id
	 * @return boolean
	 */
	public static function insert($pdo, $user_id, $game_type_id) {
		$statement = $pdo->prepare("INSERT INTO `web_logickehry_db`.`subscription` "
				. "(`user_id`, `game_type_id`) "
				. "VALUES (:uid, :gid)");
		if ($statement->execute(['uid' => $user_id, 'gid' => $game_type_id])) {
			return true;
		} else {
			var_dump($statement->errorInfo());
		}
	}

#	Mirror of model\database\tables\User

	var $user_id;
	var $orion_login;
	var $name;
	var $surname;
	var $role_id;


# Mirror of model\database\tables\GameType
	var $game_type_id = false;
	var $game_name;
	var $game_subtitle = false;
	var $avg_playtime;
	var $min_players;
	var $max_players;

}
