<?php

namespace model\database\views;

/**
 * Description of GameRatingExtended
 *
 * @author Stepan
 */
class GameRatingExtended extends \model\database\tables\GameRating {

	/**
	 * 
	 * @param \PDO $pdo
	 * @param int $user_id
	 * @param int $game_type_id
	 * @return GameRatingExtended
	 */
	public static function fetchOne($pdo, $user_id, $game_type_id) {
		$statement = $pdo->prepare("SELECT * FROM `web_logickehry_db`.`game_rating_extended` "
				. "WHERE `game_type_id` = :gid AND `user_id` = :uid;");
		if ($statement->execute(['gid' => $game_type_id, 'uid' => $user_id])) {
			return $statement->fetchObject(self::class);
		}
		var_dump($statement->errorInfo());
		return false;
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param int $user_id
	 * @return GameRatingExtended[]
	 */
	public static function fetchAllByUser($pdo, $user_id) {
		$statement = $pdo->prepare("SELECT * FROM `web_logickehry_db`.`game_rating_extended` "
				. "WHERE `user_id` = :uid;");
		if ($statement->execute(['uid' => $user_id])) {
			return $statement->fetchAll(\PDO::FETCH_CLASS, self::class);
			
		}
		var_dump($statement->errorInfo());
		return false;
	}

	/**
	 * 
	 * @param \PDO $pdo
	 * @param int $game_type_id
	 * @return GameRatingExtended[]
	 */
	public static function fetchAllByGameType($pdo, $game_type_id) {
		$statement = $pdo->prepare("SELECT * FROM `web_logickehry_db`.`game_rating_extended` "
				. "WHERE `game_type_id` = :gid;");
		if ($statement->execute(['gid' => $game_type_id])) {
			return $statement->fetchAll(\PDO::FETCH_CLASS, self::class);
			
		}
		var_dump($statement->errorInfo());
		return false;
	}
	
	var $name;
	var $orion_login;
	var $game_name;
	var $subtitle;

}
