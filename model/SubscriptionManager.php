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
	 * @param type $pw
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

}
