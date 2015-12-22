<?php

namespace model;

/**
 * Description of SubscriptionManager
 *
 * @author Stepan
 */
class SubscriptionManager {

	public static function fetchGamesByUser($pw, $uid) {
		$statement = $pw->con->prepare("SELECT game_type_id FROM subscription
			WHERE user_id = :uid");
		if ($statement->execute(['uid' => $uid])) {
			return $statement->fetchAll(\PDO::FETCH_COLUMN);
		}
		return null;
	}

	public static function fetchUsersByGame($pw, $gid) {
		$statement = $pw->con->prepare("SELECT orion_login FROM subscribees "
				. "WHERE game_type_id = :gid");
		if ($statement->execute(['gid' => $gid])) {
			return $statement->fetchAll(\PDO::FETCH_COLUMN);
		}
		return null;
	}

	/**
	 * 
	 * @param \libs\PDOwrapper $pw
	 * @return database\views\GameTypeExtended[]
	 */
	public static function fetchGamesDetailedByUser($pw, $uid) {
		$statement = $pw->con->prepare("SELECT game_type_extended.* FROM game_type_extended "
				. "JOIN subscription ON subscription.game_type_id = game_type_extended.game_type_id "
				. "WHERE subscription.user_id = :uid");
		if ($statement->execute(['uid' => $uid])) {
			return $statement->fetchAll(\PDO::FETCH_CLASS, database\views\GameTypeExtended::class);
		}
		return null;
	}

	/**
	 * @param \libs\PDOwrapper $pw
	 * @param int $user_id
	 * @param int $game_type_id
	 * @return mixed
	 */
	public static function remove($pw, $user_id, $game_type_id) {
		$statement = $pw->con->prepare("DELETE FROM subscription "
				. "WHERE user_id = :uid AND game_type_id = :gid");
		if ($statement->execute(['uid' => $user_id, 'gid' => $game_type_id])) {
			return true;
		} else {
			var_dump($statement->errorInfo());
		}
	}

	/**
	 * @param \libs\PDOwrapper $pw
	 * @param int $user_id
	 * @param int $game_type_id
	 * @return mixed
	 */
	public static function insert($pw, $user_id, $game_type_id) {
		$statement = $pw->con->prepare("INSERT INTO `web_logickehry_db`.`subscription` "
				. "(`user_id`, `game_type_id`) "
		. "VALUES (:uid, :gid)");
		if ($statement->execute(['uid' => $user_id, 'gid' => $game_type_id])) {
			return true;
		} else {
			var_dump($statement->errorInfo());
		}
	}

}
