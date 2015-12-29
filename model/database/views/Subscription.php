<?php
namespace model\database\views;


/**
 * Description of Subscription
 *
 * @author Stepan
 */
class Subscription{
	
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
	
	
#	Mirror of model\database\tables\User
	var $user_id;
	
	var $orion_login;
	
	var $name;
	
	var $surname;
	
	var $role_id;
	
	
# Mirror of model\database\tables\User
	var $game_type_id = false;
	
	var $game_name;
	
	var $subtitle = false;
	
	var $avg_playtime;
	
	var $min_players;
	
	var $max_players;
	
}